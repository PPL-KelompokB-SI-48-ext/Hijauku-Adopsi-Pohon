<?php

namespace App\Filament\FieldStaff\Resources\CareRecords\Schemas;

use App\Models\CarePlan;
use App\Models\FieldAssignment;
use App\Models\Tree;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CareRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Pilih pohon yang ditugaskan...')
                    ->options(function (): array {
                        $user = auth()->user();

                        if (! $user instanceof User || ! $user->isFieldStaff()) {
                            return [];
                        }

                        // Only show trees from this staff's assignments
                        $assignedTreeIds = FieldAssignment::query()
                            ->where('assignee_id', $user->getKey())
                            ->pluck('tree_id')
                            ->unique()
                            ->all();

                        return Tree::query()
                            ->whereIn('id', $assignedTreeIds)
                            ->with(['species', 'plot'])
                            ->get()
                            ->mapWithKeys(function (Tree $tree): array {
                                $label = $tree->code;
                                if ($tree->species) {
                                    $label .= ' - '.$tree->species->name;
                                }
                                if ($tree->plot) {
                                    $label .= ' (Petak '.$tree->plot->code.')';
                                }

                                return [$tree->id => $label];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->required()
                    ->preload()
                    ->live()
                    ->helperText('Hanya pohon yang ditugaskan kepada Anda yang ditampilkan.'),

                Select::make('plan_id')
                    ->label('Rencana Perawatan (Opsional)')
                    ->placeholder('Pilih rencana yang terkait (opsional)...')
                    ->options(function ($get): array {
                        $treeId = $get('tree_id');

                        if (! $treeId) {
                            return [];
                        }

                        return CarePlan::query()
                            ->where('tree_id', $treeId)
                            ->whereIn('status', ['draft', 'published'])
                            ->orderBy('planned_on', 'desc')
                            ->get()
                            ->mapWithKeys(function (CarePlan $plan): array {
                                $label = ($plan->planned_on?->format('d M Y') ?? '-')
                                    .' - '
                                    .Str::limit($plan->action, 50);

                                return [$plan->id => $label];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Realisasi dapat dicatat tanpa rencana. Jika terkait rencana, pilih di sini.'),

                DatePicker::make('performed_on')
                    ->label('Tanggal Pelaksanaan')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->columnSpan(1),

                FileUpload::make('photo')
                    ->label('Bukti Foto')
                    ->image()
                    ->disk('private')
                    ->directory('care-records/photos')
                    ->visibility('private')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->columnSpan(1)
                    ->helperText('Opsional. Format JPG, PNG, atau WebP, maksimal 5 MB.'),

                Textarea::make('action')
                    ->label('Deskripsi Tindakan')
                    ->placeholder('Contoh: Pemupukan organik cair 100 ml setiap pangkal pohon, kondisi tanah lembap...')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->helperText('Jelaskan tindakan perawatan yang sudah dilaksanakan.'),
            ])
            ->columns(2);
    }
}
