<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class RecommendationLetter extends Model
{
    protected $fillable = [
        'application_id',
        'referee_name',
        'referee_email',
        'token',
        'comments',
        'letter_path',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /* Relationships */

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /* Signed URL Helper */

    /**
     * Generates a cryptographically secure, temporary signed URL for the referee.
     * Valid for 14 days by default.
     */
    public function getSignedUploadUrl(): string
    {
        return URL::temporarySignedRoute(
            'referee.upload-form',
            now()->addDays(14),
            ['token' => $this->token]
        );
    }

    /**
     * Check if this letter has been submitted yet.
     */
    public function isSubmitted(): bool
    {
        return !is_null($this->submitted_at);
    }
}
