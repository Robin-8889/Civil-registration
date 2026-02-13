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
}
