<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Observers\LeadObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([LeadObserver::class])]
class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => LeadStatus::NEW->value,
    ];

    protected $fillable = [
        'name',
        'phone',
        'source',
        'status',
        'next_follow_up_at',
        'priority',
        'assigned_to',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'priority' => LeadPriority::class,
            'next_follow_up_at' => 'datetime',
            'assigned_at' => 'datetime',
        ];
    }


    // Relationships
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function statusLogs(): HasMany
    {
        return $this->hasMany(LeadStatusLog::class);
    }
    public function escalation(): HasOne
    {
        return $this->hasOne(LeadEscalation::class);
    }

    public function scopeOverdue(Builder $query)
    {
        return $query->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<', now())
            ->whereNotIn('status', [LeadStatus::WON, LeadStatus::LOST]);
    }
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }
        if ($user->isSalesManager()) {
            return $query->whereIn(
                'assigned_to',
                User::select('id')->where('team_id', $user->team_id)
            );
        }
        return $query->where('assigned_to', $user->id);
    }
}
