<?php

namespace App\Filament\Resources\LeadEscalations\Pages;

use App\Filament\Resources\LeadEscalations\LeadEscalationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadEscalations extends ListRecords
{
    protected static string $resource = LeadEscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
