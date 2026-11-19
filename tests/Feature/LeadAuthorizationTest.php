<?php

namespace Tests\Feature;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadAuthorizationTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    private int $agentRoleId;
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->agentRoleId = Role::firstOrCreate(
            ['slug' => 'sales_agent'],
            ['name' => 'Sales Agent']
        )->id;
    }

    public function test_agent_cannot_view_another_agents_lead_by_policy(): void
    {
        $agentA = User::factory()->create(['role_id' => $this->agentRoleId, 'is_active' => true]);
        $agentB = User::factory()->create(['role_id' => $this->agentRoleId, 'is_active' => true]);
        $lead = Lead::withoutEvents(fn () => Lead::factory()->create(['assigned_to' => $agentB->id]));
        $this->assertFalse($agentA->can('view', $lead));
        $this->assertTrue($agentB->can('view', $lead));
    }

    public function test_agent_cannot_open_another_agents_lead_by_url(): void
    {
        $agentA = User::factory()->create(['role_id' => $this->agentRoleId, 'is_active' => true]);
        $agentB = User::factory()->create(['role_id' => $this->agentRoleId, 'is_active' => true]);
        $lead = Lead::withoutEvents(fn () => Lead::factory()->create(['assigned_to' => $agentB->id]));
        $this->actingAs($agentA)->get(LeadResource::getUrl('edit', ['record' => $lead]))->assertNotFound();
    }
}
