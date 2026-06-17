<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One identity-verification attempt. The user's current state lives on
 * {@see User::$kyc_status}; this keeps the full history of attempts.
 */
class KycSubmission extends Model
{
    protected $table = 'kyc_submissions';

    protected $fillable = [
        'user_id', 'full_name', 'document_type', 'document_number', 'document_path',
        'message', 'status', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Human label for the document type. */
    public function getDocumentLabelAttribute(): string
    {
        return match ($this->document_type) {
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'drivers_license' => "Driver's licence",
            default => 'Official document',
        };
    }
}
