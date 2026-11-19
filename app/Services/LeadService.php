<?php

namespace App\Services;

use App\Models\AssignmentCursor;
use App\Models\Lead;
use App\Models\User;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function autoassignLeadToUser(Lead $lead)
    {
        return DB::transaction(function () use ($lead) {
            $cursor = AssignmentCursor::lockForUpdate()->first();

            if (! $cursor) {
                $cursor = AssignmentCursor::create(['last_assigned_user_id' => null]);
            }
            $lead = Lead::lockForUpdate()->find($lead->id);

            if (! $lead || $lead->assigned_to !== null) {
                return null;
            }

            $assignedAt = now();
            $startOfDay = $assignedAt->copy()->startOfDay();
            $endOfDay = $assignedAt->copy()->endOfDay();
            $agents = User::autoassignable()->orderBy('id')->lockForUpdate()->get();
            $orderedAgents = $this->agentsStartingAfterCursor($agents, $cursor->last_assigned_user_id);

            foreach ($orderedAgents as $agent) {
                if ($this->todayCount($agent, $startOfDay, $endOfDay) < $agent->daily_capacity) {
                    $lead->update([
                        'assigned_to' => $agent->id,
                        'assigned_at' => $assignedAt,
                    ]);

                    $agent->update(['last_assigned_at' => $assignedAt]);
                    $cursor->update(['last_assigned_user_id' => $agent->id]);

                    Notification::make()
                    ->title('New Lead Assigned')
                    ->body("A new lead has been assigned to you: {$lead->name}")
                    ->actions([
                        Action::make('viewLead')
                            ->label('View Lead')
                            ->url(LeadResource::getUrl('view', ['record' => $lead]))
                    ])
                    ->sendToDatabase($agent);
                    return $agent;

                }
            }

            return null;
        });
    }

    public function availableAgentsForDailyAssignment(?User $actor = null)
    {
        $agents = User::autoassignable();

        if ($actor?->isSalesManager()) {
            $agents->where('team_id', $actor->team_id);
        }

        $options = [];

        foreach ($agents->orderBy('name')->get() as $agent) {
            $remaining = $this->remainingCapacity($agent);

            if ($remaining > 0) {
                $options[$agent->id] = "{$agent->name} ({$remaining} remaining today)";
            }
        }

        return $options;
    }

    public function remainingCapacity(User $agent): int
    {
        $now = now();

        return max(0,$agent->daily_capacity - $this->todayCount($agent,$now->copy()->startOfDay(),$now->copy()->endOfDay(),),);
    }

    public function assignLeadsToUser(iterable $leads, int $agentId, ?User $actor = null)
    {
        return DB::transaction(function () use ($leads, $agentId, $actor) {
            $agentQuery = User::agents()
                ->active()
                ->whereKey($agentId)
                ->where('daily_capacity', '>', 0);
            if ($actor?->isSalesManager()) {
                $agentQuery->where('team_id', $actor->team_id);
            }
            $agent = $agentQuery->lockForUpdate()->first();
            if (! $agent) {
                return ['assigned' => 0, 'skipped' => 0, 'remaining' => 0];
            }
            $assignedAt = now();
            $remaining = $this->remainingCapacity($agent);
            $assigned = 0;
            $skipped = 0;
            foreach ($leads as $lead) {
                if ($remaining <= 0) {
                    $skipped++;
                    continue;
                }
                $lead = Lead::lockForUpdate()->find($lead->id);
                if (! $lead ||  $lead->assigned_to == $agent->id) {
                    $skipped++;
                    continue;
                }
                if ($actor && ! $actor->can('assign', $lead)) {
                    $skipped++;
                    continue;
                }
                $lead->update([
                    'assigned_to' => $agent->id,
                    'assigned_at' => $assignedAt,
                ]);
                Notification::make()
                ->title('New Lead Assigned')
                ->body("A new lead has been assigned to you: {$lead->name}")
                ->actions([
                    Action::make('viewLead')
                        ->label('View Lead')
                        ->url(LeadResource::getUrl('view', ['record' => $lead]))
                ])
                ->sendToDatabase($agent);
                $remaining--;
                $assigned++;
            }
            if ($assigned > 0) {
                $agent->update(['last_assigned_at' => $assignedAt]);
            }
            return compact('assigned', 'skipped', 'remaining');
        });
    }

    public function updateLeadForUser(Lead $lead, User $user, array $data)
    {
        return DB::transaction(function () use ($lead, $user, $data) {
            $lead = Lead::lockForUpdate()->findOrFail($lead->id);

            if (! $user->can('update', $lead)) {
                abort(403, 'You are not allowed to update this lead.');
            }
            $allowedFields = ['status', 'next_follow_up_at'];
            if (! $user->isSalesAgent()) {
                $allowedFields = [
                    'name',
                    'phone',
                    'source',
                    'status',
                    'next_follow_up_at',
                    'priority',
                ];
            }
            $lead->update(array_intersect_key($data, array_flip($allowedFields)));
            return $lead->refresh();
        });
    }

    private function todayCount(User $agent,$startOfDay = null,$endOfDay = null)
    {
        return Lead::where('assigned_to', $agent->id)
            ->where('assigned_at', '>=', $startOfDay)
            ->where('assigned_at', '<=', $endOfDay)
            ->count();
    }

    private function agentsStartingAfterCursor($agents, int|null $lastAssignedUserId)
    {
        $orderedAgents = [];
        $start = false;
        foreach ($agents as $agent) {
            if ($start) {
                $orderedAgents[] = $agent;
            }
            if ($agent->id == $lastAssignedUserId) {
                $start = true;
            }
        }
        return array_merge($orderedAgents, $agents->all());
    }
}
