<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticityScanLimit extends Model
{
    use HasFactory;

    protected $table = 'authenticity_scan_limit';

    protected $fillable = [
        'name',
        'max_scans',
        'is_active',
    ];
}
