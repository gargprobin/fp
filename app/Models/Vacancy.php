<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        'institution_id',
        'title',
        'description',
        'subject',
        'target_class',
        'experience_required',
        'location',
        'salary_min',
        'salary_max',
        'deadline',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    /* Relationships */

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /* Scopes */

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
                     ->where('deadline', '>=', now()->toDateString());
    }

    public function scopeFilterBySubject($query, ?string $subject)
    {
        if ($subject) {
            return $query->where('subject', 'like', '%' . $subject . '%');
        }
        return $query;
    }

    public function scopeFilterByLocation($query, ?string $location)
    {
        if ($location) {
            return $query->where('location', 'like', '%' . $location . '%');
        }
        return $query;
    }
}
