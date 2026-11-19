<?php

namespace App\Filament\Resources\LeadEscalations;

use App\Filament\Resources\LeadEscalations\Pages\CreateLeadEscalation;
use App\Filament\Resources\LeadEscalations\Pages\EditLeadEscalation;
use App\Filament\Resources\LeadEscalations\Pages\ListLeadEscalations;
use App\Filament\Resources\LeadEscalations\Pages\ViewLeadEscalation;
use App\Filament\Resources\LeadEscalations\Schemas\LeadEscalationForm;
use App\Filament\Resources\LeadEscalations\Schemas\LeadEscalationInfolist;
use App\Filament\Resources\LeadEscalations\Tables\LeadEscalationsTable;
use App\Models\LeadEscalation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadEscalationResource extends Resource
{
    protected static ?string $model = LeadEscalation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LeadEscalationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadEscalationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadEscalationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadEscalations::route('/'),
            'view' => ViewLeadEscalation::route('/{record}'),
        ];
    }
}
