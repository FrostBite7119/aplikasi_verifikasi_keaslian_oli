<?php

namespace Tests\Feature;

use App\Models\AuthenticityQRCode;
use App\Models\AuthenticityQRCodeScan;
use App\Models\Product;
use App\Models\Report;
use App\Models\ReportReason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected ReportReason $reportReason1;
    protected ReportReason $reportReason2;
    protected int $adminId;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Create test admin (required for foreign key constraint)
        $this->adminId = DB::table('admins')->insertGetId([
            'admin_id' => 'ADM-001',
            'name' => 'Test Admin',
            'phone_number' => '081234567890',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test product
        $this->product = Product::create([
            'product_id' => 'PROD-001',
            'name' => 'Test Oil Product',
            'type' => 'oli',
            'description' => 'High quality synthetic oil',
            'specification' => '10W-40',
            'image' => 'test-image.jpg',
            'created_by' => $this->adminId,
        ]);

        // Create test report reasons
        $this->reportReason1 = ReportReason::create([
            'report_reason_id' => 'RR-001',
            'reason' => 'Produk Palsu',
        ]);

        $this->reportReason2 = ReportReason::create([
            'report_reason_id' => 'RR-002',
            'reason' => 'Kemasan Rusak',
        ]);
    }

    public function test_it_displays_report_form_without_scan_id(): void
    {
        $response = $this->get('/report');

        $response->assertStatus(200);
        $response->assertViewIs('report');
        $response->assertViewHas('products');
        $response->assertViewHas('reportReasons');
        $response->assertViewHas('needProductColumn', true);
        $response->assertViewHas('scan', null);
    }

    public function test_it_displays_report_form_with_valid_scan_id(): void
    {
        // Create QR code
        $qrCode = AuthenticityQRCode::create([
            'code' => 'TEST-QR-001',
            'serial_number' => 'SN-001',
            'total_scans' => 1,
            'product_id' => $this->product->id,
            'created_by' => $this->adminId,
        ]);

        // Create scan
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-001',
            'qr_code' => 'TEST-QR-001',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'success',
            'authenticity_qr_code_id' => $qrCode->id,
        ]);

        $response = $this->get('/report/' . $scan->scan_id);

        $response->assertStatus(200);
        $response->assertViewIs('report');
        $response->assertViewHas('scan', function ($viewScan) use ($scan) {
            return $viewScan->scan_id === $scan->scan_id;
        });
        $response->assertViewHas('needProductColumn', false);
    }

    public function test_it_shows_product_column_for_not_found_scan_type(): void
    {
        // Create scan with not_found type
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-002',
            'qr_code' => 'INVALID-QR-001',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'not_found',
            'authenticity_qr_code_id' => null,
        ]);

        $response = $this->get('/report/' . $scan->scan_id);

        $response->assertStatus(200);
        $response->assertViewHas('needProductColumn', true);
    }

    public function test_it_redirects_when_scan_id_not_found(): void
    {
        $response = $this->get('/report/INVALID-SCAN-ID');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Tidak dapat menenukan data scan yang dimaksud.');
    }

    public function test_it_redirects_when_report_already_exists_for_scan(): void
    {
        // Create scan
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-003',
            'qr_code' => 'TEST-QR-002',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'not_found',
            'authenticity_qr_code_id' => null,
        ]);

        // Create existing report
        Report::create([
            'report_id' => 'RPT-001',
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'product_id' => $this->product->id,
            'authenticity_qr_code_scan_id' => $scan->id,
        ]);

        $response = $this->get('/report/' . $scan->scan_id);

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Laporan untuk scan ID ini sudah ada.');
    }

    public function test_it_successfully_creates_report_without_scan_id(): void
    {
        $reportData = [
            'name' => 'John Doe',
            'phone_number' => '081234567890',
            'description' => 'Saya menemukan produk palsu',
            'reportReasons' => [$this->reportReason1->id, $this->reportReason2->id],
            'product_id' => $this->product->id,
            'address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Terima kasih atas laporan Anda.');

        $this->assertDatabaseHas('reports', [
            'name' => 'John Doe',
            'phone_number' => '081234567890',
            'description' => 'Saya menemukan produk palsu',
            'product_id' => $this->product->id,
            'city' => 'Jakarta',
        ]);

        $report = Report::where('name', 'John Doe')->first();
        $this->assertCount(2, $report->reportReasons);
    }

    public function test_it_successfully_creates_report_with_scan_id(): void
    {
        // Create QR code
        $qrCode = AuthenticityQRCode::create([
            'code' => 'TEST-QR-003',
            'serial_number' => 'SN-003',
            'total_scans' => 1,
            'product_id' => $this->product->id,
            'created_by' => $this->adminId,
        ]);

        // Create scan
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-004',
            'qr_code' => 'TEST-QR-003',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Jl. Scan Location',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'scan_type' => 'success',
            'authenticity_qr_code_id' => $qrCode->id,
        ]);

        $reportData = [
            'name' => 'Jane Doe',
            'phone_number' => '082345678901',
            'description' => 'Produk mencurigakan',
            'reportReasons' => [$this->reportReason1->id],
            'scan_id' => $scan->scan_id,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'name' => 'Jane Doe',
            'phone_number' => '082345678901',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'product_id' => $this->product->id,
            'authenticity_qr_code_scan_id' => $scan->id,
        ]);
    }

    public function test_it_successfully_creates_report_with_not_found_scan_type(): void
    {
        // Create scan with not_found type
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-005',
            'qr_code' => 'INVALID-QR-002',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Jl. Scan Location',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'scan_type' => 'not_found',
            'authenticity_qr_code_id' => null,
        ]);

        $reportData = [
            'name' => 'Bob Smith',
            'phone_number' => '083456789012',
            'description' => 'QR Code tidak ditemukan',
            'reportReasons' => [$this->reportReason1->id],
            'scan_id' => $scan->scan_id,
            'product_id' => $this->product->id,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'name' => 'Bob Smith',
            'city' => 'Surabaya',
            'product_id' => $this->product->id,
            'authenticity_qr_code_scan_id' => $scan->id,
        ]);
    }

    public function test_it_successfully_creates_report_with_image(): void
    {
        $image = UploadedFile::fake()->image('report.jpg', 800, 600);

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '084567890123',
            'description' => 'Report dengan gambar',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Jl. Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $image,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $report = Report::where('name', 'Test User')->first();
        $this->assertNotNull($report->image);
        Storage::disk('local')->assertExists('report_images/' . $report->image);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->post('/report/store', []);

        $response->assertSessionHasErrors(['name', 'phone_number', 'reportReasons', 'description']);
    }

    public function test_it_validates_phone_number_format(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '1234567890', // Invalid format (should start with 08)
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['phone_number']);
    }

    public function test_it_validates_report_reasons_exist(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [9999], // Non-existent reason
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['reportReasons.0']);
    }

    public function test_it_validates_product_id_when_no_scan_id(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            // Missing product_id
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['product_id']);
    }

    public function test_it_validates_location_fields_when_no_scan_id(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            // Missing location fields
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['address', 'city', 'province', 'latitude', 'longitude']);
    }

    public function test_it_validates_image_format(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $invalidFile,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_it_prevents_duplicate_report_for_same_scan(): void
    {
        // Create scan
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-006',
            'qr_code' => 'TEST-QR-004',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'not_found',
            'authenticity_qr_code_id' => null,
        ]);

        // Create first report
        Report::create([
            'report_id' => 'RPT-002',
            'name' => 'First User',
            'phone_number' => '081234567890',
            'description' => 'First report',
            'address' => 'Test Address',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'product_id' => $this->product->id,
            'authenticity_qr_code_scan_id' => $scan->id,
        ]);

        // Try to create second report with same scan_id
        $reportData = [
            'name' => 'Second User',
            'phone_number' => '082345678901',
            'description' => 'Second report',
            'reportReasons' => [$this->reportReason1->id],
            'scan_id' => $scan->scan_id,
            'product_id' => $this->product->id, // Required for not_found scan_type
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Laporan untuk scan ID ini sudah ada.');
    }

    public function test_it_rollsback_transaction_on_error(): void
    {
        // Force an error by providing invalid data that passes validation but fails on insert
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => 9999, // Non-existent product (will pass validation but fail on foreign key)
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $initialReportCount = Report::count();

        $response = $this->post('/report/store', $reportData);

        // Should not create any report due to rollback
        $this->assertEquals($initialReportCount, Report::count());
    }

    public function test_it_deletes_uploaded_image_on_error(): void
    {
        Storage::fake('local');

        $image = UploadedFile::fake()->image('report.jpg');

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => 9999, // Invalid product to trigger error
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $image,
        ];

        $this->post('/report/store', $reportData);

        // Image should not persist in storage
        $files = Storage::disk('local')->files('report_images');
        $this->assertEmpty($files);
    }

    public function test_it_generates_unique_report_id(): void
    {
        $reportData = [
            'name' => 'User 1',
            'phone_number' => '081234567890',
            'description' => 'Test 1',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $this->post('/report/store', $reportData);

        $reportData['name'] = 'User 2';
        $reportData['phone_number'] = '082345678901';

        $this->post('/report/store', $reportData);

        $reports = Report::all();
        $this->assertCount(2, $reports);
        $this->assertNotEquals($reports[0]->report_id, $reports[1]->report_id);
        $this->assertStringStartsWith('RPT', $reports[0]->report_id);
        $this->assertStringStartsWith('RPT', $reports[1]->report_id);
    }

    public function test_it_retrieves_report_image(): void
    {
        Storage::fake('local');

        $image = UploadedFile::fake()->image('report.jpg');

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $image,
        ];

        $this->post('/report/store', $reportData);

        $report = Report::where('name', 'Test User')->first();

        $response = $this->get('/reports/image/' . $report->report_id);

        $response->assertStatus(200);
    }

    public function test_it_attaches_multiple_report_reasons(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id, $this->reportReason2->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $this->post('/report/store', $reportData);

        $report = Report::where('name', 'Test User')->first();

        $this->assertCount(2, $report->reportReasons);
        $this->assertTrue($report->reportReasons->contains($this->reportReason1));
        $this->assertTrue($report->reportReasons->contains($this->reportReason2));
    }

    public function test_it_validates_distinct_report_reasons(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id, $this->reportReason1->id], // Duplicate
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['reportReasons.1']);
    }

    public function test_it_validates_minimum_report_reasons(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [], // Empty array
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['reportReasons']);
    }

    public function test_it_handles_optional_description_field(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => '',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_it_validates_name_max_length(): void
    {
        $reportData = [
            'name' => str_repeat('A', 256), // Exceeds max length of 255
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_it_validates_phone_number_max_length(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '08' . str_repeat('1', 14), // Exceeds max length of 15
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['phone_number']);
    }

    public function test_it_accepts_various_valid_phone_number_formats(): void
    {
        $validPhoneNumbers = [
            '081234567890',
            '082345678901',
            '083456789012',
            '084567890123',
            '085678901234',
            '086789012345',
            '087890123456',
            '088901234567',
            '089012345678',
        ];

        foreach ($validPhoneNumbers as $phoneNumber) {
            $reportData = [
                'name' => 'Test User ' . $phoneNumber,
                'phone_number' => $phoneNumber,
                'description' => 'Test',
                'reportReasons' => [$this->reportReason1->id],
                'product_id' => $this->product->id,
                'address' => 'Test',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ];

            $response = $this->post('/report/store', $reportData);

            $response->assertRedirect('/');
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('reports', ['phone_number' => $phoneNumber]);
        }
    }

    public function test_it_rejects_invalid_phone_number_formats(): void
    {
        $invalidPhoneNumbers = [
            '12345678901',      // Doesn't start with 08
            '0712345678',       // Starts with 07
            '+6281234567890',   // Has country code
            '8123456789',       // Missing leading 0
            '081-2345-6789',    // Has hyphens
            '081 2345 6789',    // Has spaces
        ];

        foreach ($invalidPhoneNumbers as $phoneNumber) {
            $reportData = [
                'name' => 'Test User',
                'phone_number' => $phoneNumber,
                'description' => 'Test',
                'reportReasons' => [$this->reportReason1->id],
                'product_id' => $this->product->id,
                'address' => 'Test',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ];

            $response = $this->post('/report/store', $reportData);

            $response->assertSessionHasErrors(['phone_number']);
        }
    }

    public function test_it_handles_different_scan_types(): void
    {
        $scanTypes = ['success', 'limit_exceeded', 'not_found'];

        foreach ($scanTypes as $index => $scanType) {
            // Create QR code for valid scans
            $qrCode = null;
            if ($scanType !== 'not_found') {
                $qrCode = AuthenticityQRCode::create([
                    'code' => 'TEST-QR-TYPE-' . $index,
                    'serial_number' => 'SN-TYPE-' . $index,
                    'total_scans' => 1,
                    'product_id' => $this->product->id,
                    'created_by' => $this->adminId,
                ]);
            }

            $scan = AuthenticityQRCodeScan::create([
                'scan_id' => 'SCAN-TYPE-' . $index,
                'qr_code' => 'QR-TYPE-' . $index,
                'ip_address' => '127.0.0.1',
                'scan_location' => 'Test Location',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'scan_type' => $scanType,
                'authenticity_qr_code_id' => $qrCode?->id,
            ]);

            $response = $this->get('/report/' . $scan->scan_id);

            $response->assertStatus(200);
            $response->assertViewIs('report');
        }
    }

    public function test_it_validates_latitude_is_numeric(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => 'invalid',
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['latitude']);
    }

    public function test_it_validates_longitude_is_numeric(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 'invalid',
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['longitude']);
    }

    public function test_it_accepts_negative_coordinates(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => -106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    public function test_it_validates_invalid_scan_id(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'scan_id' => 'NON-EXISTENT-SCAN-ID',
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['scan_id']);
    }

    public function test_it_creates_report_with_null_optional_image(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test without image',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $report = Report::where('name', 'Test User')->first();
        $this->assertNull($report->image);
    }

    public function test_it_accepts_valid_image_formats(): void
    {
        $validFormats = ['jpg', 'jpeg', 'png'];

        foreach ($validFormats as $format) {
            $image = UploadedFile::fake()->image('report.' . $format);

            $reportData = [
                'name' => 'Test User ' . $format,
                'phone_number' => '081234567890',
                'description' => 'Test',
                'reportReasons' => [$this->reportReason1->id],
                'product_id' => $this->product->id,
                'address' => 'Test',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'image' => $image,
            ];

            $response = $this->post('/report/store', $reportData);

            $response->assertRedirect('/');
            $response->assertSessionHas('success');
        }
    }

    public function test_it_stores_image_with_unique_filename(): void
    {
        Storage::fake('local');

        $image1 = UploadedFile::fake()->image('report.jpg');
        $image2 = UploadedFile::fake()->image('report.jpg');

        $reportData1 = [
            'name' => 'Test User 1',
            'phone_number' => '081234567890',
            'description' => 'Test 1',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $image1,
        ];

        $this->post('/report/store', $reportData1);

        $reportData2 = [
            'name' => 'Test User 2',
            'phone_number' => '082345678901',
            'description' => 'Test 2',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'image' => $image2,
        ];

        $this->post('/report/store', $reportData2);

        $report1 = Report::where('name', 'Test User 1')->first();
        $report2 = Report::where('name', 'Test User 2')->first();

        $this->assertNotEquals($report1->image, $report2->image);
        Storage::disk('local')->assertExists('report_images/' . $report1->image);
        Storage::disk('local')->assertExists('report_images/' . $report2->image);
    }

    public function test_it_preserves_image_extension(): void
    {
        Storage::fake('local');

        $extensions = ['jpg', 'jpeg', 'png'];

        foreach ($extensions as $extension) {
            $image = UploadedFile::fake()->image('test.' . $extension);

            $reportData = [
                'name' => 'Test User ' . $extension,
                'phone_number' => '081234567890',
                'description' => 'Test',
                'reportReasons' => [$this->reportReason1->id],
                'product_id' => $this->product->id,
                'address' => 'Test',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'image' => $image,
            ];

            $this->post('/report/store', $reportData);

            $report = Report::where('name', 'Test User ' . $extension)->first();
            $this->assertStringEndsWith('.' . $extension, $report->image);
        }
    }

    public function test_it_handles_concurrent_report_submissions(): void
    {
        $reportData1 = [
            'name' => 'User A',
            'phone_number' => '081234567890',
            'description' => 'Report A',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Address A',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $reportData2 = [
            'name' => 'User B',
            'phone_number' => '082345678901',
            'description' => 'Report B',
            'reportReasons' => [$this->reportReason2->id],
            'product_id' => $this->product->id,
            'address' => 'Address B',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ];

        $response1 = $this->post('/report/store', $reportData1);
        $response2 = $this->post('/report/store', $reportData2);

        $response1->assertRedirect('/');
        $response2->assertRedirect('/');

        $this->assertDatabaseHas('reports', ['name' => 'User A']);
        $this->assertDatabaseHas('reports', ['name' => 'User B']);
    }

    public function test_it_validates_product_exists(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => 99999, // Non-existent product
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertSessionHasErrors(['product_id']);
    }

    public function test_it_creates_timestamps_for_report(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $this->post('/report/store', $reportData);

        $report = Report::where('name', 'Test User')->first();
        $this->assertNotNull($report->created_at);
        $this->assertNotNull($report->updated_at);
    }

    public function test_it_returns_404_for_non_existent_report_image(): void
    {
        $response = $this->get('/reports/image/NON-EXISTENT-REPORT-ID');

        $response->assertStatus(404);
    }

    public function test_report_relationships_are_properly_set(): void
    {
        // Create QR code and scan
        $qrCode = AuthenticityQRCode::create([
            'code' => 'TEST-QR-REL',
            'serial_number' => 'SN-REL',
            'total_scans' => 1,
            'product_id' => $this->product->id,
            'created_by' => $this->adminId,
        ]);

        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => 'SCAN-REL',
            'qr_code' => 'TEST-QR-REL',
            'ip_address' => '127.0.0.1',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'success',
            'authenticity_qr_code_id' => $qrCode->id,
        ]);

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test relationships',
            'reportReasons' => [$this->reportReason1->id, $this->reportReason2->id],
            'scan_id' => $scan->scan_id,
        ];

        $this->post('/report/store', $reportData);

        $report = Report::where('name', 'Test User')->first();

        // Test product relationship
        $this->assertInstanceOf(Product::class, $report->product);
        $this->assertEquals($this->product->id, $report->product->id);

        // Test scan relationship
        $this->assertInstanceOf(AuthenticityQRCodeScan::class, $report->authenticityQRCodeScan);
        $this->assertEquals($scan->id, $report->authenticityQRCodeScan->id);

        // Test report reasons relationship
        $this->assertCount(2, $report->reportReasons);
        $this->assertTrue($report->reportReasons->pluck('id')->contains($this->reportReason1->id));
        $this->assertTrue($report->reportReasons->pluck('id')->contains($this->reportReason2->id));
    }

    public function test_it_handles_long_text_fields(): void
    {
        $longDescription = str_repeat('This is a very long description. ', 100);
        $longAddress = str_repeat('Jl. Alamat Panjang ', 12) . 'No. 123'; // ~240 chars, within 255 limit

        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => $longDescription,
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => $longAddress,
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'name' => 'Test User',
        ]);
    }

    public function test_it_handles_special_characters_in_text_fields(): void
    {
        $reportData = [
            'name' => "Test User's Name & Co.",
            'phone_number' => '081234567890',
            'description' => 'Test <script>alert("xss")</script> with special chars: áéíóú ñ',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Jl. Test "Quotes" & Ampersand',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->post('/report/store', $reportData);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    public function test_report_id_starts_with_rpt_prefix(): void
    {
        $reportData = [
            'name' => 'Test User',
            'phone_number' => '081234567890',
            'description' => 'Test',
            'reportReasons' => [$this->reportReason1->id],
            'product_id' => $this->product->id,
            'address' => 'Test',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $this->post('/report/store', $reportData);

        $report = Report::where('name', 'Test User')->first();
        $this->assertStringStartsWith('RPT', $report->report_id);
        $this->assertEquals(16, strlen($report->report_id)); // RPT + 13 characters
    }

    public function test_it_displays_all_products_in_index(): void
    {
        // Create additional products
        $product2 = Product::create([
            'product_id' => 'PROD-002',
            'name' => 'Test Oil Product 2',
            'type' => 'oli',
            'description' => 'Another oil',
            'specification' => '5W-30',
            'image' => 'test-image-2.jpg',
            'created_by' => $this->adminId,
        ]);

        $response = $this->get('/report');

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() >= 2;
        });
    }

    public function test_it_displays_all_report_reasons_in_index(): void
    {
        // Create additional report reason
        $reportReason3 = ReportReason::create([
            'report_reason_id' => 'RR-003',
            'reason' => 'Harga Tidak Sesuai',
        ]);

        $response = $this->get('/report');

        $response->assertStatus(200);
        $response->assertViewHas('reportReasons', function ($reasons) {
            return $reasons->count() >= 3;
        });
    }
}
