<div class="hijauku-topbar-context" aria-label="Lokasi halaman">
    @if ($roleLabel === 'Pengelola')
        <span class="hijauku-topbar-context__home" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7M5 9v11h14V9M9 20v-7h6v7" />
            </svg>
        </span>
        <span>Pengelola Panel</span>
        <span class="hijauku-topbar-context__separator" aria-hidden="true">›</span>
        <span class="hijauku-topbar-context__current">Manajemen</span>
    @else
        <span>Hijauku</span>
        <span class="hijauku-topbar-context__separator" aria-hidden="true">›</span>
        <span class="hijauku-topbar-context__current">Operasional</span>
    @endif
</div>
