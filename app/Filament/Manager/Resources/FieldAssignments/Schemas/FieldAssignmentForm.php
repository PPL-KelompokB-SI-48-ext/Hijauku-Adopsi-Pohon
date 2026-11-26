<?php

namespace App\Filament\Manager\Resources\FieldAssignments\Schemas;

use App\Models\Activity;
use App\Models\Tree;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class FieldAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activity_id')
                    ->label('Kegiatan Lapangan')
                    ->relationship('activity', 'title', modifyQueryUsing: fn ($query) => $query->where('status', '!=', 'cancelled'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('tree_id', null))
                    ->validationMessages([
                        'required' => 'Kegiatan lapangan wajib dipilih.',
                    ]),

                Select::make('tree_id')
                    ->label('Pohon')
                    ->options(function (callable $get): array {
                        $activityId = $get('activity_id');
                        if (! $activityId) {
                            return [];
                        }

                        $activity = Activity::query()->find($activityId);
                        if (! $activity) {
                            return [];
                        }

                        return Tree::query()
                            ->whereHas('plot', fn ($query) => $query->where('site_id', $activity->site_id))
                            ->with('species')
                            ->get()
                            ->mapWithKeys(function (Tree $tree): array {
                                $species = $tree->species?->name ?? 'Pohon';

                                return [$tree->getKey() => "{$tree->code} ({$species})"];
                            })
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->helperText(fn (callable $get) => $get('activity_id')
                        ? 'Hanya menampilkan pohon di lokasi kegiatan yang dipilih.'
                        : 'Pilih kegiatan terlebih dahulu untuk melihat daftar pohon di lokasinya.')
                    ->validationMessages([
                        'required' => 'Pohon wajib dipilih.',
                    ]),

                Select::make('assignee_id')
                    ->label('Petugas Lapangan')
                    ->relationship('assignee', 'name', modifyQueryUsing: fn ($query) => $query->where('role', 'field_staff')->where('is_active', true))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Petugas lapangan wajib dipilih.',
                    ]),

                DateTimePicker::make('due_at')
                    ->label('Tenggat Penugasan')
                    ->required()
                    ->minDate(now())
                    ->native(false)
                    ->helperText('Tenggat penyelesaian tugas lapangan (tidak boleh di masa lalu).')
                    ->validationMessages([
                        'required' => 'Tenggat penugasan wajib diisi.',
                    ]),
            ])
            ->columns(2);
    }
}
