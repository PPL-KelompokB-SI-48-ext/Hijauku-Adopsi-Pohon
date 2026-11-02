<?php

namespace App\Filament\Manager\Widgets;

use App\Actions\CarePlans\UpdateCarePlanPriority;
use App\Models\CarePlan;
use App\Models\Site;
use App\Models\Tree;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueCarePlansWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isManager();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CarePlan::query()
                    ->overdue()
                    ->with(['tree.species', 'tree.plot.site', 'assignment.assignee'])
            )
            ->heading('Rencana Perawatan Terlambat')
            ->description('Daftar rencana perawatan yang telah melewati batas target dan belum memiliki realisasi terverifikasi.')
            ->columns([
                TextColumn::make('tree.code')
                    ->label('Pohon')
                    ->description(fn (CarePlan $record): ?string => $record->tree ? ($record->tree->species?->name.' - Petak '.$record->tree->plot?->code) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tree.plot.site.name')
                    ->label('Lokasi')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('planned_on')
                    ->label('Tanggal Target')
                    ->date('d M Y')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal', 'medium', null, '' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal', 'medium', null, '' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Tindakan')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published' => 'Terbit',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Semua Pohon')
                    ->options(fn () => Tree::query()->pluck('code', 'id'))
                    ->searchable(),

                SelectFilter::make('site_id')
                    ->label('Lokasi')
                    ->placeholder('Semua Lokasi')
                    ->options(fn () => Site::query()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            $query->whereHas('tree.plot', fn (Builder $q) => $q->where('site_id', $data['value']));
                        }

                        return $query;
                    })
                    ->searchable(),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                Action::make('change_priority')
                    ->label('Ubah Prioritas')
                    ->icon(Heroicon::OutlinedFlag)
                    ->button()
                    ->outlined()
                    ->color('warning')
                    ->size('xs')
                    ->modalHeading('Ubah Prioritas Rencana Terlambat')
                    ->modalDescription('Perbarui prioritas tindakan untuk rencana yang terlambat ini.')
                    ->modalSubmitActionLabel('Simpan Prioritas')
                    ->form([
                        Select::make('priority')
                            ->label('Tingkat Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak',
                            ])
                            ->default(fn (CarePlan $record): string => $record->priority ?? 'normal')
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan Perubahan')
                            ->placeholder('Contoh: Rencana terlambat membutuhkan penanganan segera...')
                            ->required()
                            ->minLength(3)
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Alasan perubahan akan tercatat dalam audit log sistem.'),
                    ])
                    ->action(function (CarePlan $record, array $data): void {
                        $actor = auth()->user();
                        abort_unless($actor instanceof User, 403);

                        app(UpdateCarePlanPriority::class)->handle($actor, $record, $data);

                        Notification::make()
                            ->title('Prioritas berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada rencana perawatan terlambat')
            ->emptyStateDescription('Semua rencana perawatan berjalan sesuai target atau telah terverifikasi.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('planned_on', 'asc');
    }
}
