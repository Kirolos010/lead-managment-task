<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->visible(fn (): bool => ! auth()->user()->isSalesAgent()),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->visible(fn (): bool => ! auth()->user()->isSalesAgent()),
                TextInput::make('source')
                    ->visible(fn (): bool => ! auth()->user()->isSalesAgent()),
                Select::make('status')
                    ->options(LeadStatus::class)
                    ->default(LeadStatus::NEW)
                    ->required()
                    ->hiddenOn('create'),
                Select::make('priority')
                    ->options(LeadPriority::class)
                    ->required()
                    ->visible(fn (): bool => ! auth()->user()->isSalesAgent()),
                DateTimePicker::make('next_follow_up_at')
                    ->seconds(false),
                Select::make('assigned_to')
                    ->label('Assigned user')
                    ->searchable()
                    ->placeholder('Unassigned')
                    ->options(function (?Lead $record, LeadService $service): array {
                        $options = $service->availableAgentsForDailyAssignment(auth()->user());
                        if ($record?->assigned_to && ! isset($options[$record->assigned_to])) {
                            $current = User::find($record->assigned_to);

                            if ($current) {
                                $options[$current->id] = "{$current->name} (current)";
                            }
                        }
                        return $options;
                    })
                    ->visible(fn()=>auth()->user()->can('assignAny', Lead::class))
                    ->visibleOn('update'),

            ]);
    }
}
