<?php

namespace App\Filament\Resources\LeadEscalations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadEscalationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->relationship('lead', 'name')
                    ->required(),
                TextInput::make('escalated_to')
                    ->label('Escalated To')
                    ->numeric(),
                DateTimePicker::make('escalated_at'),
            ]);
    }
}
