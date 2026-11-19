<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentCursor extends Model
{
    protected $fillable = ['last_assigned_user_id'];

    public function lastAssignedUser()
    {
        return $this->belongsTo(User::class, 'last_assigned_user_id');
    }
}
