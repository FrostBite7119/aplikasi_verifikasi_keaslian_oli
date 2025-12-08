<?php

use App\Http\Controllers\AuthenticityQRCodeScanController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Reports
Route::get('/report/{authenticity_qr_code_scan?}', [ReportController::class, 'index']);
Route::post('/report/store', [ReportController::class, 'store'])->middleware('throttle:100,60');
Route::get('/reports/image/{report:report_id}', [ReportController::class, 'getReportImage']);

// Product Verification
Route::get('/{qrcode?}', [AuthenticityQRCodeScanController::class, 'scan'])->middleware('throttle:100,60');
Route::post('/store', [AuthenticityQRCodeScanController::class, 'store']);