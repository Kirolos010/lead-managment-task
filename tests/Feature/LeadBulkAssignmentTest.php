<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadBulkAssignmentTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    private int $agentRoleId;
    private int $managerRoleId;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->agentRoleId = Role::firstOrCreate(
            ['slug' => 'sales_agent'],
            ['name' => 'Sales Agent']
        )->id;
        $this->managerRoleId = Role::firstOrCreate(
            ['slug' => 'sales_manager'],
            ['name' => 'Sales Manager']
        )->id;
    }
    public function test_bulk_assign_stops_at_daily_capacity(): void
    {
        $manager = User::factory()->create([
            'role_id' => $this->managerRoleId,
            'team_id' => 1,
        ]);
        $agent = User::factory()->create([
            'role_id' => $this->agentRoleId,
            'team_id' => 1,
            'is_active' => true,
            'daily_capacity' => 2,
        ]);
        $leads = Lead::withoutEvents(fn () => Lead::factory()->count(4)->create([]));
        $result = app(LeadService::class)->assignLeadsToUser($leads, $agent->id, $manager);
        $this->assertEquals(2, $result['assigned']);
        $this->assertEquals(2, $result['skipped']);
        $this->assertEquals(2, Lead::where('assigned_to', $agent->id)->count());
    }

    public function test_manager_cannot_assign_leads_of_another_team(): void
    {
        $manager = User::factory()->create([
            'role_id' => $this->managerRoleId,
            'team_id' => 1,
        ]);
        $agent = User::factory()->create([
            'role_id' => $this->agentRoleId,
            'team_id' => 1,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        $otherAgent = User::factory()->create([
            'role_id' => $this->agentRoleId,
            'team_id' => 2,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        $otherLead = Lead::withoutEvents(fn () => Lead::factory()->create([
            'assigned_to' => $otherAgent->id,
        ]));
        $result = app(LeadService::class)->assignLeadsToUser([$otherLead], $agent->id, $manager);
        $this->assertEquals(0, $result['assigned']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertEquals($otherAgent->id, $otherLead->fresh()->assigned_to);
    }

    public function test_manager_cannot_assign_to_agent_of_another_team(): void
    {
        $manager = User::factory()->create([
            'role_id' => $this->managerRoleId,
            'team_id' => 1,
        ]);
        $otherAgent = User::factory()->create([
            'role_id' => $this->agentRoleId,
            'team_id' => 2,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        $lead = Lead::withoutEvents(fn () => Lead::factory()->create());
        $result = app(LeadService::class)->assignLeadsToUser([$lead], $otherAgent->id, $manager);
        $this->assertEquals(0, $result['assigned']);
        $this->assertNull($lead->fresh()->assigned_to);
    }
}
