<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('source')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('next_follow_up_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('priority')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('-'),
                TextEntry::make('assigned_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Lead $record): bool => $record->trashed()),
            ]);
    }
}
