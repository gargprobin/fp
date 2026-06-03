<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'type',
        'website',
        'description',
        'location',
        'branding_settings',
    ];

    protected $casts = [
        'branding_settings' => 'array',
    ];

    /* Relationships */

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function vacancies()
    {
        return $this->hasMany(Vacancy::class);
    }

    /* Accessors for Dynamic Custom Branding */

    /**
     * Get primary color for institution branding, fallback to a elegant blue.
     */
    public function getPrimaryColorAttribute(): string
    {
        return $this->branding_settings['primary_color'] ?? '#2563eb'; // Default royal blue
    }

    /**
     * Get accent color for institution branding.
     */
    public function getAccentColorAttribute(): string
    {
        return $this->branding_settings['accent_color'] ?? '#3b82f6'; // Default sky blue
    }

    /**
     * Get custom preset theme name (e.g., emerald, amber, indigo, ruby).
     */
    public function getThemePresetAttribute(): string
    {
        return $this->branding_settings['theme_preset'] ?? 'classic';
    }
}
