<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $relatedResource = UserResource::class;

    protected static ?string $title = 'Team Users';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->placeholder('-'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('daily_capacity')
                    ->label('Daily Capacity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('assignedLeads_count')
                    ->label('Assigned Leads')
                    ->counts('assignedLeads')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
