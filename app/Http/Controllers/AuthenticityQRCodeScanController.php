<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthenticityQRCodeScanRequest;
use App\Models\AuthenticityQRCode;
use App\Models\AuthenticityQRCodeScan;
use App\Models\AuthenticityScanLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AuthenticityQRCodeScanController extends Controller
{
    private const MAX_SCANS_PER_IP = 2;
    private const STATUS_LIMIT_EXCEEDED = 'limit_exceeded';
    private const STATUS_IP_LIMIT_EXCEEDED = 'ip_limit_exceeded';
    private const STATUS_NOT_FOUND = 'not_found';
    private const STATUS_SUCCESS = 'success';

    protected string $userIP;

    public function __construct()
    {
        $this->userIP = request()->ip();
    }

    public function scan(?string $qrcode = null): View
    {
        if ($this->shouldUseLastScanData($qrcode)) {
            return $this->buildViewFromLastScan();
        }

        session()->forget('last_scan_id');

        if (!$qrcode) {
            return view('main');
        }

        if (!$this->isQRCodeValid($qrcode)) {
            return view('index', ['qrcode' => $qrcode]);
        }

        if ($this->hasExceededIpScanLimit($qrcode)) {
            return view('index', [
                'qrcode' => $qrcode,
                'error' => 'Anda telah mencapai batas maksimum scan produk. Permintaan Anda tidak dapat diproses lebih lanjut.'
            ]);
        }

        return view('index', ['qrcode' => $qrcode]);
    }

    protected function shouldUseLastScanData(?string $qrcode): bool
    {
        if (!session()->has('last_scan_id')) {
            return false;
        }

        $lastScan = AuthenticityQRCodeScan::where('scan_id', session()->get('last_scan_id'))->first();

        return $lastScan 
            && $lastScan->qr_code == $qrcode 
            && $lastScan->ip_address == $this->userIP;
    }

    protected function buildViewFromLastScan(): View
    {
        $scan = AuthenticityQRCodeScan::where('scan_id', session()->get('last_scan_id'))->first();

        return view('index', [
            'status' => $scan->scan_type,
            'data' => $this->buildProductData($scan->authenticityQRCode, AuthenticityScanLimit::first()),
            'scan_id' => $scan->scan_id,
            'skipStoreLocation' => true,
        ]);
    }

    public function store(StoreAuthenticityQRCodeScanRequest $request): JsonResponse
    {
        $data = $request->validated();

        // helper to create a scan and persist last scan id in session
        $saveScan = function (string $status, ?int $authId = null) use ($data) {
            $scan = $this->recordScan($data, $status, $authId);
            session()->put('last_scan_id', $scan->scan_id);
            return $scan;
        };

        $qrcode = $this->isQRCodeValid($data['qrcode']);

        if (!$qrcode) {
            $scan = $saveScan(self::STATUS_NOT_FOUND);

            return response()->json([
                'status' => self::STATUS_NOT_FOUND,
                'data' => null,
                'scan_id' => $scan->scan_id,
            ]);
        }

        $scanLimit = AuthenticityScanLimit::first();

        if ($scanLimit && $qrcode->total_scans > $scanLimit->max_scans) {
            $scan = $saveScan(self::STATUS_LIMIT_EXCEEDED, $qrcode->id);

            return response()->json([
                'status' => self::STATUS_LIMIT_EXCEEDED,
                'data' => ['scan_limit' => $scanLimit->max_scans],
                'scan_id' => $scan->scan_id,
            ]);
        }

        if ($this->hasExceededIpScanLimit($qrcode->code)) {
            return response()->json([
                'status' => self::STATUS_IP_LIMIT_EXCEEDED,
            ]);
        }

        $qrcode->increment('total_scans');
        $scan = $saveScan(self::STATUS_SUCCESS, $qrcode->id);

        return response()->json([
            'status' => self::STATUS_SUCCESS,
            'data' => $this->buildProductData($qrcode, $scanLimit),
            'scan_id' => $scan->scan_id,
        ]);
    }

    protected function isQRCodeValid(string $qrcode): ?AuthenticityQRCode
    {
        return AuthenticityQRCode::whereRaw('BINARY code = ?', [$qrcode])->first();
    }

    protected function hasExceededIpScanLimit(string $qrcode): bool
    {
        $totalIpScans = AuthenticityQRCodeScan::where('qr_code', $qrcode)
            ->where('ip_address', $this->userIP)
            ->count();

        return $totalIpScans >= self::MAX_SCANS_PER_IP;
    }

    protected function recordScan(array $validatedData, string $scanType, ?int $authenticityQrCodeId = null): AuthenticityQRCodeScan
    {
        $scanId = $this->generateScanId();

        $authenticityQrCodeScan = AuthenticityQRCodeScan::create([
            'scan_id' => $scanId,
            'qr_code' => $validatedData['qrcode'],
            'ip_address' => $this->userIP,
            'scan_location' => $validatedData['scan_location'],
            'city' => $validatedData['city'],
            'province' => $validatedData['province'],
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'scan_type' => $scanType,
            'authenticity_qr_code_id' => $authenticityQrCodeId,
        ]);

        return $authenticityQrCodeScan;
    }

    protected function buildProductData(AuthenticityQRCode $qrcode, AuthenticityScanLimit $scanLimit): array
    {
        return [
            'product_name' => $qrcode->product->name,
            'total_scans' => $qrcode->total_scans,
            'scan_limit' => $scanLimit->max_scans,
            'qrcode' => $qrcode->code,
            'serial_number' => $qrcode->serial_number,
            'description' => $qrcode->product->description,
            'specification' => $qrcode->product->specification,
            'product_image' => url("products/image/{$qrcode->product->product_id}"),
        ];
    }

    private function generateScanId()
    {
        do {
            $scanId = strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
        } while (AuthenticityQRCodeScan::where('scan_id', $scanId)->exists());

        return $scanId;
    }
}
