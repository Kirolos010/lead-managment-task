<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'old_status',
        'new_status',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => LeadStatus::class,
            'new_status' => LeadStatus::class,
            'changed_at' => 'datetime',
        ];
    }

    // Relationships

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
