<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme_preference',
        'phone',
        'notification_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'notification_settings' => 'array',
        ];
    }

    /**
     * Get user notification preferences with fallback defaults.
     */
    public function getNotificationSettings(): array
    {
        return is_null($this->notification_settings) ? [
            'in_app' => true,
            'whatsapp' => true,
            'email' => true,
            'dnd' => false,
            'dnd_start' => '22:00',
            'dnd_end' => '08:00',
        ] : $this->notification_settings;
    }

    /* Relationships */

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function institution()
    {
        return $this->hasOne(Institution::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }

    /* Role Helpers */

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function isInstitution(): bool
    {
        return $this->role === 'institution' || $this->role === 'recruiter';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRecruiter(): bool
    {
        return $this->role === 'recruiter' || $this->role === 'institution';
    }
}
