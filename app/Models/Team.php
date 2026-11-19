<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;


class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'manager_id',
    ];

    // Relationships
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isSalesManager()) {
            return $query->whereKey($user->team_id);
        }

        return $query->whereKey($user->team_id);
    }
}
