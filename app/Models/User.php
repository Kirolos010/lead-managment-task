<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'daily_capacity',
        'last_assigned_at',
        'role_id',
        'team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'daily_capacity' => 'integer',
            // 'last_assigned_at' => 'datetime',

        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    public function managedTeams()
    {
        return $this->hasMany(Team::class, 'manager_id');
    }
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }


    //scopes
    public function scopeManagers(Builder $query)
    {
        return $query->whereHas('role', fn(Builder $q)=> $q->where('slug', 'sales_manager'));
    }
    public function scopeAdmins(Builder $query)
    {
        return $query->whereHas('role', fn(Builder $q)=> $q->where('slug', 'admin'));
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
    public function scopeAgents(Builder $query)
    {
        return $query->whereHas('role', fn (Builder $q) => $q->where('slug', 'sales_agent'));
    }

    //assgin scope
    //have daily capacity and active and is agent

    public function scopeAutoassignable(Builder $query)
    {
        return $query->active()->agents()->where('daily_capacity', '>', 0);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }
        if ($user->isSalesManager()) {
            return $query->where('team_id', $user->team_id);
        }
        return $query->where('id', $user->id);
    }


    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }
    public function isSalesManager(): bool
    {
        return $this->role?->slug=== 'sales_manager';
    }
    public function isSalesAgent(): bool
    {
        return $this->role?->slug === 'sales_agent';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }
}
