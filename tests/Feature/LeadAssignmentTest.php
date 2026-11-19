<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Withseeding;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadAssignmentTest extends TestCase
{
    //make seed
    use RefreshDatabase ;
    protected $seed = true;
    private int $agentroleid;
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->agentroleid = Role::where('slug', 'sales_agent')->firstOrFail()->id;
    }

    public function test_round_robin_distributes_leads(): void
    {
        $agents = User::factory()->count(3)->create([
            'role_id' => $this->agentroleid,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        Lead::factory()->count(9)->create();
        // dd($agents->pluck('id')->toArray());
        foreach ($agents as $agent) {
            $this->assertEquals(3, Lead::where('assigned_to', $agent->id)->count());
        }
    }
    public function test_inactive_agent_never_receives_leads(): void
    {
        $inactive = User::factory()->create([
            'role_id' => $this->agentroleid,
            'is_active' => false,
            'daily_capacity' => 5,
        ]);
        $active = User::factory()->create([
            'role_id' => $this->agentroleid,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        Lead::factory()->count(5)->create();
        $this->assertEquals(0, Lead::where('assigned_to', $inactive->id)->count());
        $this->assertEquals(5, Lead::where('assigned_to', $active->id)->count());
    }
    public function test_agent_over_capacity_is_skipped(): void
    {
        $full = User::factory()->create([
            'role_id' => $this->agentroleid,
            'is_active' => true,
            'daily_capacity' => 1,
        ]);
        $free = User::factory()->create([
            'role_id' => $this->agentroleid,
            'is_active' => true,
            'daily_capacity' => 5,
        ]);
        Lead::factory()->create([
            'assigned_to' => $full->id,
            'assigned_at' => now(),
        ]);
        $lead = Lead::factory()->create();
        $this->assertEquals($free->id, $lead->fresh()->assigned_to);
    }
}
