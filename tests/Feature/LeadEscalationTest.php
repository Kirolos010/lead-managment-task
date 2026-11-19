<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Jobs\EscalateLeadJob;
use App\Models\Lead;
use App\Services\LeadEscalationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadEscalationTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_job_is_dispatched_when_lead_is_created(): void
    {
        $lead = Lead::factory()->create();
        Queue::assertPushed(EscalateLeadJob::class, function ($job) use ($lead) {
            return $job->leadId == $lead->id;
        });
    }

    public function test_escalation_does_nothing_if_status_changed(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::CONTACTED]);
        app(LeadEscalationService::class)->escalate($lead->id);
        $this->assertDatabaseCount('lead_escalations', 0);
    }

    public function test_running_escalation_twice_creates_one_record(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::NEW]);
        $service = app(LeadEscalationService::class);
        $service->escalate($lead->id);
        $service->escalate($lead->id);
        $this->assertDatabaseCount('lead_escalations', 1);
    }
}
