<?php

namespace App\Jobs;

use App\Services\LeadEscalationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EscalateLeadJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public array $backoff = [10, 60, 300];
    /**
     * Create a new job instance.
     */
    public function __construct(public int $leadId)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(LeadEscalationService $service): void
    {
        $service->escalate($this->leadId);
    }
}
