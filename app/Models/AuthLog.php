<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
        'success',
        'failure_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'success' => 'boolean',
    ];

    /**
     * Get the user that owns the auth log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful authentication attempts.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed authentication attempts.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for specific actions.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
