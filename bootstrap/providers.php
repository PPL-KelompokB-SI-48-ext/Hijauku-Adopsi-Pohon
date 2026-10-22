<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CitizenPanelProvider;
use App\Providers\Filament\FieldStaffPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FieldStaffPanelProvider::class,
    CitizenPanelProvider::class,
];
