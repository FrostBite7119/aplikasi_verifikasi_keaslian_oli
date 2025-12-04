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

    public function store(StoreAuthenticityQRCodeScanRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $qrcode = $this->isQRCodeValid($validatedData['qrcode']);
        
        if (!$qrcode) {
            $this->recordScan($validatedData, self::STATUS_NOT_FOUND);
            return response()->json([
                'status' => self::STATUS_NOT_FOUND,
                'data' => null
            ]);
        }

        $scanLimit = AuthenticityScanLimit::first();

        if ($qrcode->total_scans > $scanLimit->max_scans) {
            $this->recordScan($validatedData, self::STATUS_LIMIT_EXCEEDED, $qrcode->id);
            return response()->json([
                'status' => self::STATUS_LIMIT_EXCEEDED,
                'data' => ['scan_limit' => $scanLimit->max_scans]
            ]);
        }

        if ($this->hasExceededIpScanLimit($qrcode->code)) {
            return response()->json([
                'status' => self::STATUS_IP_LIMIT_EXCEEDED,
                'message' => 'Anda telah mencapai batas maksimum scan produk dari alamat IP ini. Permintaan Anda tidak dapat diproses lebih lanjut.'
            ]);
        }

        $qrcode->increment('total_scans');
        $this->recordScan($validatedData, self::STATUS_SUCCESS, $qrcode->id);

        return response()->json([
            'status' => self::STATUS_SUCCESS,
            'data' => $this->buildProductData($qrcode, $scanLimit)
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

    protected function recordScan(array $validatedData, string $scanType, ?int $authenticityQrCodeId = null): void
    {
        AuthenticityQRCodeScan::create([
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
}
