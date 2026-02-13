<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarriageRecord extends Model
{
    protected $fillable = [
        'groom_id',
        'bride_id',
        'registration_office_id',
        'marriage_certificate_no',
        'date_of_marriage',
        'place_of_marriage',
        'witness1_name',
        'witness2_name',
        'registration_date',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->marriage_certificate_no)) {
                $model->marriage_certificate_no = self::generateCertificateNumber($model->registration_date);
            }
        });
    }

    public static function generateCertificateNumber($registrationDate)
    {
        $year = date('Y', strtotime($registrationDate));
        $prefix = 'MA';

        // Count existing marriage records for this year (excluding rejected)
        $lastRecord = self::whereYear('registration_date', $year)
            ->where('status', '!=', 'rejected')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord) {
            // Extract the last 5-digit number from existing certificate
            $lastNumber = (int) substr($lastRecord->marriage_certificate_no, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $sequentialNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$sequentialNumber}";
    }

    public function groom()
    {
        return $this->belongsTo(BirthRecord::class, 'groom_id');
    }

    public function bride()
    {
        return $this->belongsTo(BirthRecord::class, 'bride_id');
    }

    public function office()
    {
        return $this->belongsTo(RegistrationOffice::class, 'registration_office_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'record_id')
            ->where('record_type', 'marriage');
    }
}
