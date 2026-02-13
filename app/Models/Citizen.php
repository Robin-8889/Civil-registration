<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    protected $fillable = [
        'national_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'place_of_birth',
        'address',
        'phone',
        'email',
    ];

    public function birthRecords()
    {
        return $this->hasMany(BirthRecord::class, 'child_id');
    }

    public function asGroom()
    {
        return $this->hasMany(MarriageRecord::class, 'groom_id');
    }

    public function asBride()
    {
        return $this->hasMany(MarriageRecord::class, 'bride_id');
    }

    public function deathRecords()
    {
        return $this->hasMany(DeathRecord::class, 'deceased_id');
    }
}
