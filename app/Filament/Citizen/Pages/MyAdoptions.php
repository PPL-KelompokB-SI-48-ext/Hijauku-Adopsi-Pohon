<?php

namespace App\Filament\Citizen\Pages;

use App\Actions\Adoptions\AcceptAdoptionAgreementAction;
use App\Actions\Adoptions\WithdrawAdoptionRequestAction;
use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class MyAdoptions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'adopsi-saya';

    protected static ?string $navigationLabel = 'Adopsi Saya';

    protected static string|UnitEnum|null $navigationGroup = 'Adopsi';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.citizen.pages.my-adoptions';

    public string $activeTab = 'agreements';

    public function getTitle(): string
    {
        return $this->activeTab === 'agreements'
            ? 'Kesepakatan Adopsi Saya'
            : 'Permohonan Adopsi Saya';
    }

    public function getHeader(): ?View
    {
        return $this->activeTab === 'agreements'
            ? view('filament.citizen.pages.my-adoptions-header')
            : null;
    }

    public function getSubheading(): ?string
    {
        return $this->activeTab === 'agreements'
            ? 'Perjanjian komitmen adopsi pohon resmi dengan pengelola yang siap disepakati dan dipantau.'
            : 'Pantau permohonan adopsi pohon Anda dalam satu halaman.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Ajukan Adopsi')
                ->icon('heroicon-o-plus')
                ->url(url('/warga/adoption-requests/create'))
                ->visible(fn (): bool => $this->activeTab === 'requests'),
        ];
    }

    public function switchTab(string $tab): void
    {
        if (! in_array($tab, ['requests', 'agreements'], true)) {
            return;
        }

        $this->activeTab = $tab;

        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->activeTab === 'requests'
                ? AdoptionRequest::query()
                    ->where('requester_id', auth()->id())
                    ->with(['tree.species', 'tree.plot'])
                : AdoptionAgreement::query()
                    ->where('adopter_id', auth()->id())
                    ->with(['tree.species', 'tree.plot']))
            ->columns($this->activeTab === 'requests'
                ? $this->requestColumns()
                : $this->agreementColumns())
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->options(fn (): array => $this->activeTab === 'requests'
                        ? [
                            'draft' => 'Draf',
                            'submitted' => 'Menunggu Keputusan',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'withdrawn' => 'Ditarik',
                        ]
                        : [
                            'awaiting_acceptance' => 'Menunggu Persetujuan Warga',
                            'active' => 'Aktif',
                            'completed' => 'Selesai',
                            'terminated' => 'Dihentikan',
                        ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersResetAction(
                fn (Action $action): Action => $action
                    ->label('')
                    ->icon('heroicon-o-arrow-path')
                    ->tooltip('Reset filter')
            )
            ->recordActions($this->activeTab === 'requests'
                ? $this->requestActions()
                : $this->agreementActions())
            ->searchPlaceholder(
                $this->activeTab === 'requests'
                    ? 'Cari kode pohon atau jenis pohon...'
                    : 'Cari kode pohon/perjanjian...'
            )
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 20, 50])
            ->defaultPaginationPageOption(20)
            ->striped()
            ->emptyStateHeading(fn (): string => $this->activeTab === 'agreements'
                ? 'Belum ada kesepakatan adopsi'
                : 'Belum ada permohonan adopsi')
            ->emptyStateDescription(fn (): string => $this->activeTab === 'agreements'
                ? 'Kesepakatan akan muncul setelah permohonan adopsi disetujui pengelola.'
                : 'Permohonan adopsi pohon yang Anda ajukan akan ditampilkan di sini.');
    }

    protected function agreementColumns(): array
    {
        return [
            TextColumn::make('tree.code')
                ->label('POHON')
                ->description(fn (AdoptionAgreement $record): string => trim(
                    ($record->tree?->species?->name ?? 'Jenis pohon tidak tersedia')
                    .' · Petak '.($record->tree?->plot?->code ?? '-')
                ))
                ->searchable(),

            TextColumn::make('id')
                ->label('NO. KESEPAKATAN')
                ->formatStateUsing(fn ($state, AdoptionAgreement $record): string => 'AGR-'.($record->starts_on?->format('Y') ?? $record->created_at?->format('Y'))
                    .'-'.str_pad((string) $state, 3, '0', STR_PAD_LEFT))
                ->fontFamily('mono')
                ->searchable(),

            TextColumn::make('starts_on')
                ->label('PERIODE ADOPSI')
                ->formatStateUsing(fn ($state, AdoptionAgreement $record): string => ($record->starts_on?->format('d M Y') ?? '-')
                    .' - '
                    .($record->ends_on?->format('d M Y') ?? 'Tidak ditentukan'))
                ->description(fn (AdoptionAgreement $record): string => $record->status === 'completed'
                        ? 'Masa Adopsi Berakhir (Selesai)'
                        : 'Periode Kesepakatan')
                ->wrap(),

            TextColumn::make('terms')
                ->label('KETENTUAN (RINGKAS)')
                ->formatStateUsing(fn (?string $state): string => Str::limit($state ?? '-', 85))
                ->wrap(),

            TextColumn::make('status')
                ->label('STATUS KESEPAKATAN')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'awaiting_acceptance' => 'Menunggu Persetujuan Warga',
                    'active' => 'Aktif',
                    'completed' => 'Selesai',
                    'terminated' => 'Dihentikan',
                    default => Str::headline($state),
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'awaiting_acceptance' => 'warning',
                    'active' => 'success',
                    'completed' => 'info',
                    'terminated' => 'danger',
                    default => 'gray',
                })
                ->sortable(),

            TextColumn::make('accepted_at')
                ->label('DITERIMA PADA')
                ->date('d M Y')
                ->placeholder('-')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function requestColumns(): array
    {
        return [
            TextColumn::make('tree.code')
                ->label('KODE POHON')
                ->searchable(),

            TextColumn::make('tree.species.name')
                ->label('JENIS POHON')
                ->searchable(),

            TextColumn::make('reason')
                ->label('ALASAN PERMOHONAN')
                ->limit(65)
                ->wrap(),

            TextColumn::make('status')
                ->label('STATUS')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Draf',
                    'submitted' => 'Menunggu Keputusan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'withdrawn' => 'Ditarik',
                    default => Str::headline($state),
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'submitted' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'withdrawn' => 'gray',
                    default => 'info',
                }),

            TextColumn::make('created_at')
                ->label('TANGGAL')
                ->date('d M Y'),
        ];
    }

    protected function agreementActions(): array
    {
        return [
            Action::make('view')
                ->label('Lihat')
                ->icon('heroicon-o-eye')
                ->modalHeading('Detail Kesepakatan Adopsi')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn (AdoptionAgreement $record) => view(
                    'filament.citizen.pages.partials.adoption-agreement-details',
                    ['record' => $record]
                )),

            Action::make('accept')
                ->label('Terima Kesepakatan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Terima Kesepakatan Adopsi')
                ->modalDescription('Dengan menerima kesepakatan ini, Anda menyetujui komitmen dan periode adopsi yang tercantum.')
                ->modalSubmitActionLabel('Terima Kesepakatan')
                ->visible(fn (AdoptionAgreement $record): bool => $record->status === 'awaiting_acceptance'
                    && auth()->user()->can('accept', $record))
                ->action(function (AdoptionAgreement $record): void {
                    app(AcceptAdoptionAgreementAction::class)
                        ->handle(auth()->user(), $record);

                    $this->resetTable();
                }),
        ];
    }

    protected function requestActions(): array
    {
        return [
            Action::make('view')
                ->label('Lihat')
                ->icon('heroicon-o-eye')
                ->url(fn (AdoptionRequest $record): string => url('/warga/adoption-requests/'.$record->getKey())),

            Action::make('withdraw')
                ->label('Tarik')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (AdoptionRequest $record): bool => $record->status === 'submitted'
                    && auth()->user()->can('withdraw', $record))
                ->action(function (AdoptionRequest $record): void {
                    app(WithdrawAdoptionRequestAction::class)
                        ->handle(auth()->user(), $record);

                    $this->resetTable();
                }),
        ];
    }
}
