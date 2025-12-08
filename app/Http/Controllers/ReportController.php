<?php

namespace App\Http\Controllers;

use App\Models\AuthenticityQRCodeScan;
use App\Models\Product;
use App\Models\Report;
use App\Models\ReportReason;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(?string $authenticity_qr_code_scan = null)
    {
        $authenticityQRCodeScan = null;
        $needProductColumn = true;

        if ($authenticity_qr_code_scan) {
            $authenticityQRCodeScan = AuthenticityQRCodeScan::where('scan_id', $authenticity_qr_code_scan)->first();

            if (!$authenticityQRCodeScan) {
                return redirect('/')->with('error', 'Tidak dapat menenukan data scan yang dimaksud.');
            }

            if ($authenticityQRCodeScan->report()->exists()) {
                return redirect('/')->with('error', 'Laporan untuk scan ID ini sudah ada.');
            }

            $needProductColumn = $authenticityQRCodeScan->scan_type === 'not_found';
        }

        return view('report', [
            'products' => Product::all(),
            'reportReasons' => ReportReason::all(),
            'scan' => $authenticityQRCodeScan,
            'needProductColumn' => $needProductColumn
        ]);
    }

    public function store(StoreReportRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $imageName = null;
            $imagePath = null;
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('report_images', $imageName);
            }

            // Get location data from scan or request
            $scanId = $validatedData['scan_id'] ?? null;
            $authenticityQRCodeScan = null;
            
            if ($scanId) {
                $authenticityQRCodeScan = AuthenticityQRCodeScan::where('scan_id', $scanId)->first();

                if($authenticityQRCodeScan->report()->exists())
                {
                    DB::rollBack();
                    return redirect('/')->with('error', 'Laporan untuk scan ID ini sudah ada.');
                }

                $address = $authenticityQRCodeScan->scan_location;
                $city = $authenticityQRCodeScan->city;
                $province = $authenticityQRCodeScan->province;
                $latitude = $authenticityQRCodeScan->latitude;
                $longitude = $authenticityQRCodeScan->longitude;
                $productId = $authenticityQRCodeScan->scan_type == 'not_found' 
                    ? ($validatedData['product_id'])
                    : $authenticityQRCodeScan->authenticityQRCode->product_id;
            } else {
                $address = $validatedData['address'];
                $city = $validatedData['city'];
                $province = $validatedData['province'];
                $latitude = $validatedData['latitude'];
                $longitude = $validatedData['longitude'];
                $productId = $validatedData['product_id'];
            }

            // Create the report
            $report = Report::create([
                'report_id' => 'RPT' . strtoupper(Str::random(13)),
                'name' => $validatedData['name'],
                'phone_number' => $validatedData['phone_number'],
                'description' => $validatedData['description'] ?? '',
                'image' => $imageName,
                'address' => $address,
                'city' => $city,
                'province' => $province,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'product_id' => $productId,
                'authenticity_qr_code_scan_id' => $authenticityQRCodeScan?->id,
            ]);

            // Attach report reasons
            $report->reportReasons()->attach($validatedData['reportReasons']);

            DB::commit();

            return redirect('/')->with('success', 'Laporan berhasil dikirim. Terima kasih atas laporan Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if exists
            if ($imagePath && Storage::disk('local')->exists($imagePath)) {
                Storage::disk('local')->delete($imagePath);
            }
            
            return redirect()->back()
            ->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.' . $e->getMessage()]);
        }
    }

    public function getReportImage(Report $report)
    {
        $response = Storage::response("report_images/{$report->image}");

        return $response;
    }
}
