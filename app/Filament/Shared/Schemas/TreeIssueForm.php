<?php

namespace App\Filament\Shared\Schemas;

use App\Models\Tree;
use App\Models\TreeIssue;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TreeIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tree_id')
                    ->label('Pohon')
                    ->options(function (): array {
                        $reporter = auth()->user();

                        if (! $reporter instanceof User) {
                            return [];
                        }

                        return Tree::query()
                            ->reportableBy($reporter)
                            ->with('species')
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (Tree $tree): array => [
                                $tree->getKey() => $tree->code.' - '.($tree->species?->name ?? 'Jenis tidak tersedia'),
                            ])
                            ->all();
                    })
                    ->native()
                    ->live()
                    ->required()
                    ->helperText('Pilihan hanya menampilkan pohon adopsi aktif atau pohon yang ditugaskan kepada Anda.'),

                Select::make('severity')
                    ->label('Tingkat Kebutuhan Penanganan')
                    ->options(TreeIssue::SEVERITY_OPTIONS)
                    ->native()
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi Masalah')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                FileUpload::make('evidence')
                    ->label('Bukti Foto')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->required()
                    ->storeFiles(false)
                    ->columnSpanFull()
                    ->helperText('Wajib diisi. Format JPG, PNG, atau WebP, maksimal 5 MB.'),
            ])
            ->columns(2);
    }
}
