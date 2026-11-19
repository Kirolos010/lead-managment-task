<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')->visibleOn('create')
                    ->minLength(8)
                    ->maxLength(150),
                TextInput::make('daily_capacity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                Select::make('role_id')
                    ->relationship(
                        'role',
                        'name',
                        fn ($query) => $query->visibleTo(auth()->user()),
                    )
                    ->default(fn (): ?int => auth()->user()->isSalesManager()
                        ? Role::where('slug', 'sales_agent')->value('id')
                        : null)
                    ->required(),
                Select::make('team_id')
                    ->relationship(
                        'team',
                        'name',
                        fn ($query) => $query->visibleTo(auth()->user()),
                    )
                    ->default(fn (): ?int => auth()->user()->isSalesManager()
                        ? auth()->user()->team_id
                        : null)
                    ->required(),
            ]);
    }
}
