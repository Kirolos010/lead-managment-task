<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadConcurrencyTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    private function makeAgent(int $capacity): User
    {
        $roleId = Role::firstOrCreate(
            ['slug' => 'sales_agent'],
            ['name' => 'Sales Agent']
        )->id;

        return User::factory()->create([
            'role_id' => $roleId,
            'is_active' => true,
            'daily_capacity' => $capacity,
        ]);
    }

    public function test_competing_assignments_never_exceed_capacity(): void
    {
        $agent = $this->makeAgent(5);
        $leads = Lead::withoutEvents(fn () => Lead::factory()->count(10)->create());
        $service = app(LeadService::class);
        foreach ($leads as $lead) {
            $service->autoassignLeadToUser($lead);
        }
        $this->assertEquals(5, Lead::where('assigned_to', $agent->id)->count());
        $this->assertEquals(5, Lead::whereNull('assigned_to')->count());
    }

    public function test_same_lead_is_never_assigned_twice(): void
    {
        $this->makeAgent(5);
        $this->makeAgent(5);
        $lead = Lead::withoutEvents(fn () => Lead::factory()->create());
        $service = app(LeadService::class);
        $first = $service->autoassignLeadToUser($lead);
        $second = $service->autoassignLeadToUser($lead);
        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals($first->id, $lead->fresh()->assigned_to);
    }
}
