<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('manager_id')
                    ->relationship(
                        name: 'manager',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) =>$query->managers()->active(),
                    ),
            ]);
    }
}
