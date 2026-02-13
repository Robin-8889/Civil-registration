<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationOffice extends Model
{
    protected $fillable = [
        'office_name',
        'location',
        'district',
        'region',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function birthRecords()
    {
        return $this->hasMany(BirthRecord::class);
    }

    public function marriageRecords()
    {
        return $this->hasMany(MarriageRecord::class);
    }

    public function deathRecords()
    {
        return $this->hasMany(DeathRecord::class);
    }
}
