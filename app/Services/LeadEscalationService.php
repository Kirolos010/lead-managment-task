<?php
namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEscalation;
use App\Models\User;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class LeadEscalationService
{
    public function escalate(int $leadId): void
    {
        DB::transaction(function () use ($leadId) {
            $lead = Lead::whereKey($leadId)->lockForUpdate()->first();
            $admin = User::admins()->active()->first();

            if (! $lead || $lead->status !== LeadStatus::NEW || ! $admin) {
                return;
            }
            $escalation = LeadEscalation::firstOrCreate(
                ['lead_id' => $lead->id],
                [
                    'escalated_to' => $admin->id,
                    'escalated_at' => now(),
                ]
            );
            if ($escalation->wasRecentlyCreated) {
                Notification::make()
                ->title('Lead Escalated')
                ->body("Lead {$lead->name} has been escalated to you.")
                ->actions([
                    Action::make('viewLead')
                        ->label('View Lead')
                        ->url(LeadResource::getUrl('view', ['record' => $lead]))
                ])
                ->sendToDatabase($admin);





            }
        });
    }
}
