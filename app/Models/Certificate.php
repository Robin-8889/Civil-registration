<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'record_id',
        'record_type',
        'certificate_number',
        'issue_date',
        'expiry_date',
        'issued_by',
        'copies_issued',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    // Polymorphic relationship to get the related record
    public function record()
    {
        if ($this->record_type === 'birth') {
            return $this->belongsTo(BirthRecord::class, 'record_id');
        } elseif ($this->record_type === 'marriage') {
            return $this->belongsTo(MarriageRecord::class, 'record_id');
        } elseif ($this->record_type === 'death') {
            return $this->belongsTo(DeathRecord::class, 'record_id');
        }
        return null;
    }
}
