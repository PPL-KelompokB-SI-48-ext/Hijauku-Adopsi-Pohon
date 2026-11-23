<div class="space-y-4 text-sm">
    <div>
        <p class="text-xs text-gray-500">Pohon</p>
        <p class="font-semibold text-gray-950 dark:text-white">
            {{ $record->tree?->code ?? '-' }}
        </p>
        <p class="text-gray-500">
            {{ $record->tree?->species?->name ?? '-' }}
            · Petak {{ $record->tree?->plot?->code ?? '-' }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs text-gray-500">Nomor Kesepakatan</p>
            <p class="font-medium">
                AGR-{{ $record->starts_on?->format('Y') ?? $record->created_at?->format('Y') }}-{{ str_pad((string) $record->getKey(), 3, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">Status</p>
            <p class="font-medium">{{ \Illuminate\Support\Str::headline($record->status) }}</p>
        </div>

        <div>
            <p class="text-xs text-gray-500">Mulai Adopsi</p>
            <p class="font-medium">{{ $record->starts_on?->format('d M Y') ?? '-' }}</p>
        </div>

        <div>
            <p class="text-xs text-gray-500">Selesai Adopsi</p>
            <p class="font-medium">{{ $record->ends_on?->format('d M Y') ?? 'Tidak ditentukan' }}</p>
        </div>
    </div>

    <div>
        <p class="text-xs text-gray-500">Ketentuan Kesepakatan</p>
        <div class="mt-1 whitespace-pre-line rounded-lg border border-gray-200 p-3 dark:border-gray-700">
            {{ $record->terms }}
        </div>
    </div>

    <div>
        <p class="text-xs text-gray-500">Diterima Pada</p>
        <p class="font-medium">{{ $record->accepted_at?->format('d M Y H:i') ?? '-' }}</p>
    </div>
</div>