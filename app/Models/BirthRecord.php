<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthRecord extends Model
{
    protected $fillable = [
        'registration_office_id',
        'birth_certificate_no',
        'date_of_birth',
        'place_of_birth',
        'child_first_name',
        'child_middle_name',
        'child_last_name',
        'gender',
        'father_name',
        'mother_name',
        'nationality',
        'registration_date',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->birth_certificate_no)) {
                $model->birth_certificate_no = self::generateCertificateNumber($model->registration_date);
            }
        });
    }

    public static function generateCertificateNumber($registrationDate)
    {
        $year = date('Y', strtotime($registrationDate));
        $prefix = 'BIR';

        // Count existing birth records for this year (excluding those with pending/rejected status being skipped in generation)
        $lastRecord = self::whereYear('registration_date', $year)
            ->where('status', '!=', 'rejected')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord) {
            // Extract the last 5-digit number from existing certificate
            $lastNumber = (int) substr($lastRecord->birth_certificate_no, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $sequentialNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$sequentialNumber}";
    }

    public function office()
    {
        return $this->belongsTo(RegistrationOffice::class, 'registration_office_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'record_id')
            ->where('record_type', 'birth');
    }

    public function deathRecord()
    {
        return $this->hasOne(DeathRecord::class, 'deceased_birth_id');
    }

    public function marriageAsGroom()
    {
        return $this->hasMany(MarriageRecord::class, 'groom_id', 'id');
    }

    public function marriageAsBride()
    {
        return $this->hasMany(MarriageRecord::class, 'bride_id', 'id');
    }
}
