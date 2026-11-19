<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('old_status')
                    ->label('Old Status')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('new_status')
                    ->label('New Status')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('user.name')
                    ->label('Changed By')
                    ->placeholder('System'),
                TextColumn::make('changed_at')
                    ->label('Changed At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('changed_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
