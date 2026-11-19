<?php

namespace App\Filament\Resources\LeadEscalations\Pages;

use App\Filament\Resources\LeadEscalations\LeadEscalationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeadEscalation extends ViewRecord
{
    protected static string $resource = LeadEscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
