<?php

namespace App\Http\Requests;

use App\Models\AuthenticityQRCodeScan;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|regex:/^08\d+$/|max:15',
            'reportReasons' => 'required|array|min:1',
            'reportReasons.*' => 'exists:report_reasons,id|distinct',
            'description' => 'required|string',
            'image' => 'nullable|mimes:jpeg,png,jpg',
            'scan_id' => 'nullable|exists:authenticity_qr_code_scans,scan_id',
            'product_id' => 'nullable|exists:products,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];

        // If scan_id is not provided, product_id and location fields are required
        if (!$this->has('scan_id')) {
            $rules['product_id'] = 'required|exists:products,id';
            $rules['address'] = 'required|string|max:255';
            $rules['city'] = 'required|string';
            $rules['province'] = 'required|string';
            $rules['latitude'] = 'required|numeric';
            $rules['longitude'] = 'required|numeric';
        } else {
            // If scan_id is provided, check if scan_type is 'not_found'
            $scan = AuthenticityQRCodeScan::where('scan_id', $this->input('scan_id'))->first();
            if ($scan && $scan->scan_type === 'not_found') {
                $rules['product_id'] = 'required|exists:products,id';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.regex' => 'Format nomor telepon tidak valid. Harus dimulai dengan "08" diikuti 18 digit angka.',
            'phone_number.max' => 'Nomor telepon maksimal terdiri dari 15 karakter.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'product_id.required' => 'Produk wajib dipilih.',
            'reportReasons.required' => 'Setidaknya satu alasan laporan harus dipilih.',
            'reportReasons.*.exists' => 'Alasan laporan yang dipilih tidak valid.',
            'reportReasons.*.distinct' => 'Alasan laporan tidak boleh duplikat.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, atau jpg.',
            'image.max' => 'Ukuran gambar maksimal adalah 2MB.',
            'scan_id.exists' => 'Scan ID tidak valid.',
            'address.city.province.latitude.longitude.required_if' => 'Gagal menyimpan laporan. Silahkan muat ulang halaman dan coba lagi.',
            'latitude.longitude.numeric' => 'Gagal menyimpan laporan. Silahkan muat ulang halaman dan coba lagi.',
            'address.max' => 'Alamat maksimal 255 karakter.',
        ];
    }
}
