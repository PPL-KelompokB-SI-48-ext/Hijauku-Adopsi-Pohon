<x-filament-panels::header
    :actions="$this->getCachedHeaderActions()"
    :actions-alignment="$this->getHeaderActionsAlignment()"
    :breadcrumbs="filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : []"
    :heading="$this->getHeading()"
    :subheading="$this->getSubheading()"
>
    <x-slot name="heading">
        <span class="mb-2 flex items-center gap-1.5 text-xs font-semibold tracking-wide text-green-700">
            <x-heroicon-o-shield-check aria-hidden="true" class="h-3.5 w-3.5 shrink-0" />
            <span>Dokumen Legalitas &amp; Komitmen</span>
        </span>
        <span class="block">{{ $this->getHeading() }}</span>
    </x-slot>
</x-filament-panels::header>
