<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'report_id',
        'name',
        'phone_number',
        'description',
        'image',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'product_id',
        'authenticity_qr_code_scan_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function authenticityQRCodeScan()
    {
        return $this->belongsTo(AuthenticityQRCodeScan::class, 'authenticity_qr_code_scan_id');
    }

    public function reportReasons()
    {
        return $this->belongsToMany(ReportReason::class, 'report_report_reason');
    }
}
