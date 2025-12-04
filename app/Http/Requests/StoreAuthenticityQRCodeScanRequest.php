<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthenticityQRCodeScanRequest extends FormRequest
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
        return [
            'qrcode' => 'required|string',
            'scan_location' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'qrcode.required' => 'Data QR Code tidak boleh kosong.',
            'scan_location.required' => 'Data lokasi tidak boleh kosong.',
            'city.required' => 'Data kota tidak boleh kosong.',
            'province.required' => 'Data provinsi tidak boleh kosong.',
            'latitude.required' => 'Data koordinat tidak boleh kosong.',
            'longitude.required' => 'Data koordinat tidak boleh kosong.',
        ];
    }
}
