<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()||$user->isSalesManager()||$user->isSalesAgent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        if($user->isAdmin()){
            return true;
        }
        if($user->isSalesManager()){
            return $user->team_id == $lead->assignedUser?->team_id;
        }
        if($user->isSalesAgent()){
            return $user->id == $lead->assigned_to;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || ($user->isSalesManager() && $user->team_id == $lead->assignedUser?->team_id) || ($user->isSalesAgent() && $user->id == $lead->assigned_to);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lead $lead): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }
    public function assignAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSalesManager();
    }

    public function assign(User $user, Lead $lead): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSalesManager()) {
            $leadTeamId = $lead->assignedUser?->team_id;

            return $leadTeamId === null
                || (int) $leadTeamId === (int) $user->team_id;
        }

        return false;
    }
}
