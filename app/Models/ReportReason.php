<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    protected $table = 'report_reasons';

    protected $fillable = [
        'report_reason_id',
        'reason',
    ];
}
