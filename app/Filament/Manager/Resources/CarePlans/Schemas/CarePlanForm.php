<?php

namespace App\Filament\Manager\Resources\CarePlans\Schemas;

use App\Models\FieldAssignment;
use App\Models\Tree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CarePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tree_id')
                    ->label('Pohon')
                    ->placeholder('Pilih pohon...')
                    ->options(function () {
                        return Tree::query()
                            ->with(['species', 'plot'])
                            ->get()
                            ->mapWithKeys(function (Tree $tree) {
                                $label = $tree->code;
                                if ($tree->species) {
                                    $label .= ' - ' . $tree->species->name;
                                }
                                if ($tree->plot) {
                                    $label .= ' (Petak ' . $tree->plot->code . ')';
                                }
                                return [$tree->id => $label];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->preload(),

                Select::make('assignment_id')
                    ->label('Penugasan Lapangan (Opsional)')
                    ->placeholder('Pilih penugasan (opsional)...')
                    ->options(function () {
                        return FieldAssignment::query()
                            ->with('assignee')
                            ->get()
                            ->mapWithKeys(function (FieldAssignment $assignment) {
                                $label = 'TGS-' . str_pad((string) $assignment->id, 4, '0', STR_PAD_LEFT);
                                if ($assignment->assignee) {
                                    $label .= ' (' . $assignment->assignee->name . ')';
                                }
                                return [$assignment->id => $label];
                            });
                    })
                    ->searchable()
                    ->nullable()
                    ->preload()
                    ->helperText('Rencana bisa langsung dibuat tanpa penugasan (D-001).'),

                DatePicker::make('planned_on')
                    ->label('Tanggal Target')
                    ->required()
                    ->native(false)
                    ->columnSpan(1),

                Textarea::make('action')
                    ->label('Aksi Perawatan')
                    ->placeholder('Contoh: Pemupukan organik berkala dan penggemburan tanah...')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->helperText('Jelaskan tindakan teknis perawatan yang direncanakan.'),
            ])
            ->columns(2);
    }
}
