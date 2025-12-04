<?php

use App\Http\Controllers\AuthenticityQRCodeScanController;
use Illuminate\Support\Facades\Route;

Route::get('/{qrcode?}', [AuthenticityQRCodeScanController::class, 'scan']);
Route::post('/store', [AuthenticityQRCodeScanController::class, 'store']);
