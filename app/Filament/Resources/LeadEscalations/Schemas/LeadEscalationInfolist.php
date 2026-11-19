<?php

namespace App\Filament\Resources\LeadEscalations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadEscalationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('lead.id')
                    ->label('Lead ID'),
                TextEntry::make('lead.name')
                    ->label('Lead Name')
                    ->placeholder('-'),
                TextEntry::make('lead.phone')
                    ->label('Phone')
                    ->placeholder('-'),
                TextEntry::make('lead.source')
                    ->label('Source')
                    ->placeholder('-'),
                TextEntry::make('lead.status')
                    ->label('Status')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('lead.priority')
                    ->label('Priority')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('lead.next_follow_up_at')
                    ->label('Next Follow-up')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lead.assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('-'),
                TextEntry::make('lead.assigned_at')
                    ->label('Assigned At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lead.created_at')
                    ->label('Lead Created At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lead.updated_at')
                    ->label('Lead Updated At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lead.deleted_at')
                    ->label('Lead Deleted At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('escalatedTo.name')
                    ->label('Escalated To')
                    ->placeholder('-'),
                TextEntry::make('escalated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
