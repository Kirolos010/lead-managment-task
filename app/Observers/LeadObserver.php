<?php

namespace App\Observers;

use App\Jobs\EscalateLeadJob;
use App\Models\Lead;
use App\Models\LeadStatusLog;
use App\Services\LeadService;
use Filament\Notifications\Notification;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        app(LeadService::class)->autoassignLeadToUser($lead);
        EscalateLeadJob::dispatch($lead->id)->delay(now()->addMinutes(30));
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        $oldStatus = $lead->getOriginal('status');
        $newStatus = $lead->status?->value ?? $lead->status;

        if ($oldStatus->value === null || (string) $oldStatus->value=== (string) $newStatus) {
            return;
        }

        LeadStatusLog::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => now(),
        ]);
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        //
    }
}
