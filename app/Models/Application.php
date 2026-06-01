<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'vacancy_id',
        'candidate_id',
        'cover_letter',
        'status',
        'demo_rating',
    ];

    protected $casts = [
        'demo_rating' => 'decimal:1',
    ];

    /* Relationships */

    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function recommendationLetters()
    {
        return $this->hasMany(RecommendationLetter::class);
    }

    /* Helper Methods */

    public function hasDemoRating(): bool
    {
        return !is_null($this->demo_rating);
    }
}
