<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadPriority;
use App\Models\Lead;
use App\Services\LeadService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('source'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('priority')
                    ->badge(),
                TextColumn::make('assignedUser.name')
                    ->label('Assigned User')
                    ->placeholder('Unassigned'),
                TextColumn::make('next_follow_up_at')
                    ->label('Time Until Follow-up')
                    ->state(fn (Lead $record): ?string => $record->next_follow_up_at?->diffForHumans())
                    ->color(fn (Lead $record): string => $record->next_follow_up_at?->isPast() ? 'danger' : 'success')
                    ->placeholder('Not scheduled')
                    ->sortable(),
                TextColumn::make('assigned_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                Filter::make('my_leads')
                    ->label('My Leads')
                    ->query(fn (Builder $query) => $query->where('assigned_to', auth()->id())),

                Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_to')),

                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('next_follow_up_at')
                        ->where('next_follow_up_at', '<', now())),

                Filter::make('high_priority')
                    ->label('High Priority')
                    ->query(fn (Builder $query) => $query->where('priority', LeadPriority::HIGH)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label('Assign to agent')
                        ->icon('heroicon-o-user-plus')
                        ->authorize('assignAny', Lead::class)
                        ->schema([
                            Select::make('agent_id')
                                ->label('Sales agent')
                                ->options(fn (LeadService $service): array => $service->availableAgentsForDailyAssignment(auth()->user()))
                                ->required()
                                ->searchable(),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Assign selected leads')
                        ->modalDescription('Only leads within the selected agent daily capacity will be assigned.')
                        ->modalSubmitActionLabel('Confirm assignment')
                        ->action(function (Collection $records, array $data, LeadService $service): void {
                            $user = auth()->user();
                            $allowed = $records->filter(fn (Lead $lead) => $user->can('assign', $lead));
                            $denied = $records->count() - $allowed->count();
                            $result = $service->assignLeadsToUser(
                                $allowed,
                                (int) $data['agent_id'],
                                $user,
                            );
                            if ($result['assigned'] === 0) {
                                Notification::make()
                                    ->title('No leads assigned')
                                    ->body('The agent is at full capacity, unavailable, or you are not allowed to assign the selected leads.')
                                    ->danger()
                                    ->send();

                                return;
                            }
                            $skipped = $result['skipped'] + $denied;
                            Notification::make()
                                ->title("Assigned {$result['assigned']} leads")
                                ->body($skipped > 0
                                    ? "{$skipped} leads were skipped (capacity, already assigned, or not permitted)."
                                    : "{$result['remaining']} assignment(s) remaining today.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
