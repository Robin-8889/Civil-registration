<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeathRecord extends Model
{
    protected $fillable = [
        'deceased_birth_id',
        'informant_birth_id',
        'registration_office_id',
        'death_certificate_no',
        'date_of_death',
        'place_of_death',
        'cause_of_death',
        'informant_name',
        'informant_relation',
        'registration_date',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->death_certificate_no)) {
                $model->death_certificate_no = self::generateCertificateNumber($model->registration_date);
            }
        });
    }

    public static function generateCertificateNumber($registrationDate)
    {
        $year = date('Y', strtotime($registrationDate));
        $prefix = 'DE';

        // Count existing death records for this year (excluding rejected)
        $lastRecord = self::whereYear('registration_date', $year)
            ->where('status', '!=', 'rejected')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord) {
            // Extract the last 5-digit number from existing certificate
            $lastNumber = (int) substr($lastRecord->death_certificate_no, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $sequentialNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$sequentialNumber}";
    }

    public function deceased()
    {
        return $this->belongsTo(BirthRecord::class, 'deceased_birth_id');
    }

    public function informant()
    {
        return $this->belongsTo(BirthRecord::class, 'informant_birth_id');
    }

    public function office()
    {
        return $this->belongsTo(RegistrationOffice::class, 'registration_office_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'record_id')
            ->where('record_type', 'death');
    }
}
