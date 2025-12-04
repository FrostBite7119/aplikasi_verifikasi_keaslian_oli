<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticityQRCodeScan extends Model
{
    use HasFactory;

    protected $table = 'authenticity_qr_code_scans';

    protected $fillable = [
        'qr_code',
        'ip_address',
        'scan_location',
        'city',
        'province',
        'latitude',
        'longitude',
        'scan_type',
        'authenticity_qr_code_id',
    ];

    public function authenticityQRCode()
    {
        return $this->belongsTo(AuthenticityQRCode::class, 'authenticity_qr_code_id');
    }
}
