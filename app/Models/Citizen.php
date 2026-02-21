<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Citizen extends Model
{
    protected $table = 'citizens';

    protected $fillable = [
        'birth_record_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'birth_certificate_no',
        'place_of_birth',
        'birth_registration_date',
        'father_name',
        'mother_name',
        'nationality',
        'registration_office_id',
        'region',
        'record_status',
        'is_married',
        'marriage_record_id',
        'marriage_certificate_no',
        'marriage_date',
        'is_dead',
        'death_record_id',
        'death_certificate_no',
        'death_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Calculate age of the citizen
     */
    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}

