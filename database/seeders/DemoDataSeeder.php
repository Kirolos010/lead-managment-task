<?php

namespace Database\Seeders;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\AssignmentCursor;
use App\Models\Lead;
use App\Models\LeadEscalation;
use App\Models\LeadStatusLog;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $managerRole = Role::where('slug', 'sales_manager')->firstOrFail();
        $agentRole = Role::where('slug', 'sales_agent')->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'daily_capacity' => 0,
                'role_id' => $adminRole->id,
                'team_id' => null,
            ],
        );

        $teamModels = collect(['Team Cairo', 'Team Giza', 'Team Alex'])
            ->map(fn (string $name) => Team::firstOrCreate(['name' => $name]));

        $managers = collect();

        foreach ($teamModels as $index => $team) {
            $manager = User::updateOrCreate(
                ['email' => 'manager'.($index + 1).'@demo.com'],
                [
                    'name' => fake()->name(),
                    'password' => Hash::make('12345678'),
                    'is_active' => true,
                    'daily_capacity' => 0,
                    'role_id' => $managerRole->id,
                    'team_id' => $team->id,
                ],
            );

            $team->update(['manager_id' => $manager->id]);
            $managers->push($manager);
        }

        $agents = collect();

        foreach ($teamModels as $teamIndex => $team) {
            for ($i = 1; $i <= 5; $i++) {
                $agents->push(User::updateOrCreate(
                    ['email' => "agent{$teamIndex}{$i}@demo.com"],
                    [
                        'name' => fake()->name(),
                        'password' => Hash::make('password'),
                        'is_active' => fake()->boolean(90),
                        'daily_capacity' => fake()->numberBetween(5, 12),
                        'role_id' => $agentRole->id,
                        'team_id' => $team->id,
                        'last_assigned_at' => null,
                    ],
                ));
            }
        }

        $activeAgents = $agents->where('is_active', true)->values();
        $dailyCounts = [];

        foreach ($activeAgents as $agent) {
            $dailyCounts[$agent->id] = 0;
        }

        $lastAgentId = null;

        for ($i = 1; $i <= 120; $i++) {
            $status = fake()->randomElement(LeadStatus::cases());
            $assignedAgent = null;
            $assignedAt = null;

            if ($activeAgents->isNotEmpty() && fake()->boolean(85)) {
                $availableAgents = $activeAgents->filter(
                    fn (User $agent) => $dailyCounts[$agent->id] < $agent->daily_capacity,
                );

                if ($availableAgents->isNotEmpty()) {
                    $lastIndex = $lastAgentId
                        ? $availableAgents->search(fn (User $agent) => $agent->id === $lastAgentId)
                        : false;

                    $assignedAgent = $lastIndex === false
                        ? $availableAgents->first()
                        : $availableAgents->get(($lastIndex + 1) % $availableAgents->count()) ?? $availableAgents->first();

                    $assignedAt = fake()->dateTimeBetween('-30 days', 'now');
                    $dailyCounts[$assignedAgent->id] = $dailyCounts[$assignedAgent->id] + 1;
                    $lastAgentId = $assignedAgent->id;
                }
            }

            $lead = Lead::withoutEvents(fn () => Lead::create([
                'name' => fake()->name(),
                'phone' => fake()->numerify('01#########'),
                'source' => fake()->randomElement(['facebook', 'website', 'instagram', 'linkedin', 'referral']),
                'status' => $status,
                'next_follow_up_at' => fake()->optional(0.8)->dateTimeBetween('-10 days', '+20 days'),
                'priority' => fake()->randomElement(LeadPriority::cases()),
                'assigned_to' => $assignedAgent?->id,
                'assigned_at' => $assignedAt,
            ]));

            $this->seedStatusLogs($lead);

            if ($status === LeadStatus::NEW && fake()->boolean(35)) {
                LeadEscalation::create([
                    'lead_id' => $lead->id,
                    'escalated_to' => fake()->randomElement([$admin->id, ...$managers->pluck('id')->all()]),
                    'escalated_at' => fake()->dateTimeBetween('-20 days', 'now'),
                ]);
            }
        }

        foreach ($activeAgents as $agent) {
            $lastAssignment = Lead::where('assigned_to', $agent->id)
                ->whereNotNull('assigned_at')
                ->max('assigned_at');

            $agent->update(['last_assigned_at' => $lastAssignment]);
        }

        AssignmentCursor::updateOrCreate(
            ['id' => 1],
            ['last_assigned_user_id' => $lastAgentId],
        );

        $this->seedNotifications($admin, $managers, $agents);
    }

    private function seedStatusLogs(Lead $lead): void
    {
        $statuses = LeadStatus::cases();
        $oldStatus = fake()->randomElement($statuses);

        for ($i = 0; $i < fake()->numberBetween(1, 3); $i++) {
            $newStatus = fake()->randomElement($statuses);

            LeadStatusLog::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_to ?: null,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);

            $oldStatus = $newStatus;
        }
    }

    private function seedNotifications(User $admin, $managers, $agents): void
    {
        $users = collect([$admin])->concat($managers)->concat($agents);

        foreach ($users->take(15) as $user) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'title' => fake()->sentence(4),
                    'body' => fake()->sentence(10),
                ]),
                'read_at' => fake()->boolean(40)
                    ? now()->subHours(fake()->numberBetween(1, 72))
                    : null,
                'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
