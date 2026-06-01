<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'current_designation',
        'highest_degree',
        'subjects_taught',
        'experience_summary',
        'experience_years',
        'google_scholar_url',
        'h_index',
        'demo_video_url',
        'cv_path',
        'current_ctc',
        'expected_ctc',
        'preferred_location',
    ];

    protected $casts = [
        'subjects_taught' => 'array',
        'experience_years' => 'decimal:1',
        'h_index' => 'integer',
        'current_ctc' => 'decimal:2',
        'expected_ctc' => 'decimal:2',
    ];

    /* Relationships */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* Scopes */

    /**
     * Scope to find profiles matching a vacancy's requirements.
     */
    public function scopeMatchForVacancy($query, Vacancy $vacancy)
    {
        return $query->where('type', $vacancy->institution->type === 'coaching_center' ? 'coaching' : 'academic')
                     ->whereJsonContains('subjects_taught', $vacancy->subject)
                     ->where('experience_years', '>=', (float) $vacancy->experience_required);
    }
}
