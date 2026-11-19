<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'escalated_to',
        'escalated_at',
    ];

    protected function casts(): array
    {
        return [
            'escalated_at' => 'datetime',
        ];
    }

    // Relationships

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
