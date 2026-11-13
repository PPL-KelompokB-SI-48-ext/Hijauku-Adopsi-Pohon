<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests\Pages;

use App\Filament\Citizen\Resources\AdoptionRequests\AdoptionRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAdoptionRequest extends ViewRecord
{
    protected static string $resource = AdoptionRequestResource::class;

    protected static ?string $title = 'Detail Permohonan Adopsi';

    protected static ?string $breadcrumb = 'Detail';
}