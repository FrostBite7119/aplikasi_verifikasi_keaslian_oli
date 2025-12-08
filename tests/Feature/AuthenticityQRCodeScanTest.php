<?php

namespace Tests\Feature;

use App\Models\AuthenticityQRCode;
use App\Models\AuthenticityQRCodeScan;
use App\Models\AuthenticityScanLimit;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticityQRCodeScanTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected AuthenticityQRCode $qrCode;
    protected AuthenticityScanLimit $scanLimit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test admin first (required for foreign key constraint)
        $adminId = DB::table('admins')->insertGetId([
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
            'created_by' => $adminId,
        ]);

        // Create test QR code
        $this->qrCode = AuthenticityQRCode::create([
            'code' => 'TEST-QR-CODE-123',
            'serial_number' => 'SN-123456',
            'total_scans' => 0,
            'product_id' => $this->product->id,
            'created_by' => $adminId,
        ]);

        // Create scan limit
        $this->scanLimit = AuthenticityScanLimit::create([
            'name' => 'Default Scan Limit',
            'max_scans' => 5,
            'is_active' => true,
        ]);
    }

    public function test_it_displays_main_view_when_no_qrcode_provided(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('main');
    }

    public function test_it_displays_index_view_with_valid_qrcode(): void
    {
        $response = $this->get('/' . $this->qrCode->code);

        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewHas('qrcode', $this->qrCode->code);
        $response->assertViewMissing('error');
    }

    public function test_it_displays_index_view_with_invalid_qrcode(): void
    {
        $response = $this->get('/INVALID-QR-CODE');

        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewHas('qrcode', 'INVALID-QR-CODE');
    }

    public function test_it_successfully_scans_valid_qrcode(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'product_name' => $this->product->name,
                'total_scans' => 1,
                'scan_limit' => $this->scanLimit->max_scans,
                'qrcode' => $this->qrCode->code,
                'serial_number' => $this->qrCode->serial_number,
                'description' => $this->product->description,
                'specification' => $this->product->specification,
            ],
        ]);
        $response->assertJsonStructure(['scan_id']);

        // Verify scan was recorded
        $this->assertDatabaseHas('authenticity_qr_code_scans', [
            'qr_code' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'scan_type' => 'success',
            'authenticity_qr_code_id' => $this->qrCode->id,
        ]);

        // Verify scan_id was generated
        $scan = AuthenticityQRCodeScan::where('qr_code', $this->qrCode->code)->first();
        $this->assertNotNull($scan->scan_id);
        $this->assertEquals(16, strlen($scan->scan_id));

        // Verify total_scans was incremented
        $this->assertEquals(1, $this->qrCode->fresh()->total_scans);
    }

    public function test_it_returns_not_found_for_invalid_qrcode(): void
    {
        $scanData = [
            'qrcode' => 'INVALID-QR-CODE',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'not_found',
            'data' => null,
        ]);
        $response->assertJsonStructure(['scan_id']);

        // Verify scan was recorded with not_found type
        $this->assertDatabaseHas('authenticity_qr_code_scans', [
            'qr_code' => 'INVALID-QR-CODE',
            'scan_type' => 'not_found',
            'authenticity_qr_code_id' => null,
        ]);

        // Verify scan_id was generated
        $scan = AuthenticityQRCodeScan::where('qr_code', 'INVALID-QR-CODE')->first();
        $this->assertNotNull($scan->scan_id);
        $this->assertEquals(16, strlen($scan->scan_id));
    }

    public function test_it_returns_limit_exceeded_when_scan_limit_reached(): void
    {
        // Set total_scans to exceed the limit
        $this->qrCode->update(['total_scans' => 10]);

        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'limit_exceeded',
            'data' => [
                'scan_limit' => $this->scanLimit->max_scans,
            ],
        ]);
        $response->assertJsonStructure(['scan_id']);

        // Verify scan was recorded with limit_exceeded type
        $this->assertDatabaseHas('authenticity_qr_code_scans', [
            'qr_code' => $this->qrCode->code,
            'scan_type' => 'limit_exceeded',
            'authenticity_qr_code_id' => $this->qrCode->id,
        ]);

        // Verify scan_id was generated
        $scan = AuthenticityQRCodeScan::where('qr_code', $this->qrCode->code)
            ->where('scan_type', 'limit_exceeded')
            ->first();
        $this->assertNotNull($scan->scan_id);
        $this->assertEquals(16, strlen($scan->scan_id));

        // Verify total_scans was NOT incremented
        $this->assertEquals(10, $this->qrCode->fresh()->total_scans);
    }

    public function test_it_returns_ip_limit_exceeded_when_ip_scans_more_than_twice(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        // Create 2 scans from the same IP (127.0.0.1) to reach the limit
        for ($i = 0; $i < 2; $i++) {
            AuthenticityQRCodeScan::create([
                'scan_id' => strtoupper(substr(bin2hex(random_bytes(8)), 0, 16)),
                'qr_code' => $this->qrCode->code,
                'ip_address' => '127.0.0.1',
                'scan_location' => 'Previous Location',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'scan_type' => 'success',
                'authenticity_qr_code_id' => $this->qrCode->id,
            ]);
        }

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ip_limit_exceeded',
        ]);
    }

    public function test_it_displays_error_on_scan_page_when_ip_limit_exceeded(): void
    {
        // Create 2 scans from the same IP to reach the limit
        for ($i = 0; $i < 2; $i++) {
            AuthenticityQRCodeScan::create([
                'scan_id' => strtoupper(substr(bin2hex(random_bytes(8)), 0, 16)),
                'qr_code' => $this->qrCode->code,
                'ip_address' => '127.0.0.1',
                'scan_location' => 'Previous Location',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'scan_type' => 'success',
                'authenticity_qr_code_id' => $this->qrCode->id,
            ]);
        }

        $response = $this->get('/' . $this->qrCode->code);

        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewHas('error', 'Anda telah mencapai batas maksimum scan produk. Permintaan Anda tidak dapat diproses lebih lanjut.');
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/store', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'qrcode',
            'scan_location',
            'city',
            'province',
            'latitude',
            'longitude',
        ]);
    }

    public function test_it_validates_latitude_and_longitude_are_numeric(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => 'not-a-number',
            'longitude' => 'not-a-number',
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_it_is_case_sensitive_for_qrcode(): void
    {
        $scanData = [
            'qrcode' => strtolower($this->qrCode->code), // lowercase version
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        // Should return not_found because QR code is case-sensitive
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'not_found',
            'data' => null,
        ]);
        $response->assertJsonStructure(['scan_id']);
    }

    public function test_it_increments_total_scans_correctly(): void
    {
        $this->assertEquals(0, $this->qrCode->total_scans);

        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        // First scan
        $this->postJson('/store', $scanData);
        $this->assertEquals(1, $this->qrCode->fresh()->total_scans);

        // Second scan from different IP
        $this->postJson('/store', $scanData, ['REMOTE_ADDR' => '192.168.1.1']);
        $this->assertEquals(2, $this->qrCode->fresh()->total_scans);
    }

    public function test_it_records_ip_address_correctly(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $this->postJson('/store', $scanData);

        $this->assertDatabaseHas('authenticity_qr_code_scans', [
            'qr_code' => $this->qrCode->code,
            'ip_address' => '127.0.0.1', // Default test IP
        ]);
    }

    public function test_it_allows_up_to_two_scans_from_same_ip(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        // First scan - should succeed
        $response1 = $this->postJson('/store', $scanData);
        $response1->assertJson(['status' => 'success']);

        // Second scan - should succeed
        $response2 = $this->postJson('/store', $scanData);
        $response2->assertJson(['status' => 'success']);

        // Third scan - should fail with IP limit exceeded
        $response3 = $this->postJson('/store', $scanData);
        $response3->assertJson(['status' => 'ip_limit_exceeded']);
    }

    public function test_it_stores_last_scan_id_in_session(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        
        // Verify session has last_scan_id
        $this->assertNotNull(session('last_scan_id'));
        
        // Verify the scan_id from response matches the one in session
        $scanId = $response->json('scan_id');
        $this->assertEquals($scanId, session('last_scan_id'));
    }

    public function test_it_stores_last_scan_id_for_not_found_status(): void
    {
        $scanData = [
            'qrcode' => 'INVALID-QR-CODE',
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'not_found']);
        
        // Verify session has last_scan_id even for not_found
        $this->assertNotNull(session('last_scan_id'));
        
        $scanId = $response->json('scan_id');
        $this->assertEquals($scanId, session('last_scan_id'));
    }

    public function test_it_stores_last_scan_id_for_limit_exceeded_status(): void
    {
        // Set total_scans to exceed the limit
        $this->qrCode->update(['total_scans' => 10]);

        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        $response = $this->postJson('/store', $scanData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'limit_exceeded']);
        
        // Verify session has last_scan_id even for limit_exceeded
        $this->assertNotNull(session('last_scan_id'));
        
        $scanId = $response->json('scan_id');
        $this->assertEquals($scanId, session('last_scan_id'));
    }

    public function test_it_uses_cached_data_when_accessing_same_qrcode_with_session(): void
    {
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];

        // First, make a scan to create session
        $scanResponse = $this->postJson('/store', $scanData);
        $scanId = $scanResponse->json('scan_id');

        // Verify session was set
        $this->assertEquals($scanId, session('last_scan_id'));

        // Now access the scan page with the same QR code
        $response = $this->get('/' . $this->qrCode->code);

        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewHas('status', 'success');
        $response->assertViewHas('scan_id', $scanId);
        $response->assertViewHas('skipStoreLocation', true);
        
        // Verify the data is from the cached scan
        $viewData = $response->viewData('data');
        $this->assertEquals($this->product->name, $viewData['product_name']);
        $this->assertEquals($this->qrCode->code, $viewData['qrcode']);
    }

    public function test_it_clears_session_when_accessing_different_qrcode(): void
    {
        // Create another QR code
        $anotherQrCode = AuthenticityQRCode::create([
            'code' => 'ANOTHER-QR-CODE-456',
            'serial_number' => 'SN-654321',
            'total_scans' => 0,
            'product_id' => $this->product->id,
            'created_by' => DB::table('admins')->first()->id,
        ]);

        // First scan to set session
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];
        $this->postJson('/store', $scanData);
        
        // Verify session is set
        $this->assertNotNull(session('last_scan_id'));

        // Access different QR code
        $response = $this->get('/' . $anotherQrCode->code);

        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewMissing('status'); // Should not have cached data
        $response->assertViewMissing('skipStoreLocation');
        
        // Session should be cleared
        $this->assertNull(session('last_scan_id'));
    }

    public function test_it_clears_session_when_accessing_without_qrcode(): void
    {
        // First scan to set session
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];
        $this->postJson('/store', $scanData);
        
        // Verify session is set
        $this->assertNotNull(session('last_scan_id'));

        // Access main page without QR code
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('main');
        
        // Session should be cleared
        $this->assertNull(session('last_scan_id'));
    }

    public function test_it_does_not_use_cached_data_from_different_ip(): void
    {
        // Create scan from IP 192.168.1.1
        $scanData = [
            'qrcode' => $this->qrCode->code,
            'scan_location' => 'Test Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ];
        
        // Manually create a scan with different IP but set it in session
        $scan = AuthenticityQRCodeScan::create([
            'scan_id' => strtoupper(substr(bin2hex(random_bytes(8)), 0, 16)),
            'qr_code' => $this->qrCode->code,
            'ip_address' => '192.168.1.1', // Different IP
            'scan_location' => 'Previous Location',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'scan_type' => 'success',
            'authenticity_qr_code_id' => $this->qrCode->id,
        ]);
        
        // Set the session to this scan_id
        session(['last_scan_id' => $scan->scan_id]);

        // Access from default IP (127.0.0.1)
        $response = $this->get('/' . $this->qrCode->code);

        $response->assertStatus(200);
        $response->assertViewIs('index');
        // Should not use cached data because IP is different
        $response->assertViewMissing('status');
        $response->assertViewMissing('skipStoreLocation');
    }
}
