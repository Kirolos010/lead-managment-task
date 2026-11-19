<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignedLeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedLeads';

    protected static ?string $relatedResource = LeadResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Lead ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('source')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('priority')
                    ->badge()
                    ->searchable(),
                TextColumn::make('next_follow_up_at')
                    ->label('Next Follow-up')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('assigned_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
