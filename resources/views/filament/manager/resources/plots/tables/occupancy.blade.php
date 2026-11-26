@php
    $progress = min(100, max(0, $percentage));
    $progressColor = $percentage >= 100 ? 'bg-sky-600' : 'bg-green-700';
@endphp

<div class="flex min-w-56 items-center gap-3">
    <span class="w-20 shrink-0 whitespace-nowrap text-sm font-medium tabular-nums text-gray-950 dark:text-white">
        {{ number_format($count) }} Pohon
    </span>

    <div class="flex min-w-24 flex-1 items-center gap-2">
        <div
            aria-label="Kapasitas petak {{ $record->code }}"
            aria-valuemax="100"
            aria-valuemin="0"
            aria-valuenow="{{ $progress }}"
            aria-valuetext="{{ $percentage }}% terisi"
            class="h-1.5 flex-1 overflow-hidden rounded-full bg-primary-100 dark:bg-primary-950"
            role="progressbar"
        >
            <div class="h-full rounded-full {{ $progressColor }}" style="width: {{ $progress }}%"></div>
        </div>
        <span class="w-10 shrink-0 text-right text-xs tabular-nums text-gray-500 dark:text-gray-400">
            {{ $percentage }}%
        </span>
    </div>
</div>
