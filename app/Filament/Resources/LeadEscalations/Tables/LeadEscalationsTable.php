<?php

namespace App\Filament\Resources\LeadEscalations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadEscalationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.id')
                    ->label('Lead ID')
                    ->sortable(),
                TextColumn::make('lead.name')
                    ->label('Lead Name')
                    ->searchable(),
                TextColumn::make('lead.phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('lead.source')
                    ->label('Source')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('lead.status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('lead.priority')
                    ->label('Priority')
                    ->badge()
                    ->searchable(),
                TextColumn::make('lead.next_follow_up_at')
                    ->label('Next Follow-up')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('lead.assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('-'),
                TextColumn::make('lead.assigned_at')
                    ->label('Assigned At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('lead.created_at')
                    ->label('Lead Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lead.updated_at')
                    ->label('Lead Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lead.deleted_at')
                    ->label('Lead Deleted At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                TextColumn::make('escalatedTo.name')
                    ->label('Escalated To')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('escalated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
