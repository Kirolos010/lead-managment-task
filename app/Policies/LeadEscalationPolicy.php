<?php

namespace App\Policies;

use App\Models\LeadEscalation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadEscalationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeadEscalation $leadEscalation): bool
    {
        return $user->isAdmin() || $user->id === $leadEscalation->escalated_to;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeadEscalation $leadEscalation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeadEscalation $leadEscalation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeadEscalation $leadEscalation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeadEscalation $leadEscalation): bool
    {
        return false;
    }
}
