<?php
// app/Http/Requests/CashTransactionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CashTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'islemTarihi' => 'required|date',
            'odeme_yonu' => 'required',
            'odeme_sekli' => 'required',
            'odeme_turu' => 'required',
            'odeme_durum' => 'required|in:1,2',
            'fiyat' => 'required|numeric',
            'aciklama' => 'nullable|string|max:500',
            'personeller' => 'nullable|integer',
            'servis' => 'nullable|integer',
            'servisler' => 'nullable|integer',
            'tedarikciler' => 'nullable|integer',
            'markalar' => 'nullable|integer',
            'cihazlar' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'islemTarihi.required' => 'İşlem tarihi zorunludur.',
            'islemTarihi.date' => 'Geçerli bir tarih giriniz.',
            'odeme_yonu.required' => 'Ödeme yönü seçiniz.',
            'odeme_yonu.in' => 'Geçersiz ödeme yönü.',
            'odeme_sekli.required' => 'Ödeme şekli seçiniz.',
            'odeme_sekli.exists' => 'Geçersiz ödeme şekli.',
            'odeme_turu.required' => 'Ödeme türü seçiniz.',
            'odeme_turu.exists' => 'Geçersiz ödeme türü.',
            'odeme_durum.required' => 'Ödeme durumu seçiniz.',
            'odeme_durum.in' => 'Geçersiz ödeme durumu.',
            'fiyat.required' => 'Tutar zorunludur.',
            'fiyat.numeric' => 'Tutar sayısal bir değer olmalıdır.',
            'fiyat.min' => 'Tutar en az :min olmalıdır.',
            'fiyat.max' => 'Tutar çok yüksek.',
            'aciklama.max' => 'Açıklama en fazla :max karakter olabilir.',
        ];
    }

    /**
     * AJAX için JSON response
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->ajax() || $this->wantsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası.',
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }

    /**
     * XSS temizliği ve fiyat formatı düzeltme
     */
    protected function prepareForValidation(): void
    {
        $cleaned = [];
        
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = $this->cleanInput($value, $key);
            } elseif (is_array($value)) {
                $cleaned[$key] = $this->cleanArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        // Fiyat formatını düzelt (1.234,56 -> 1234.56)
        if (isset($cleaned['fiyat']) && is_string($cleaned['fiyat'])) {
            $cleaned['fiyat'] = str_replace(',', '.', str_replace('.', '', $cleaned['fiyat']));
        }

        $this->merge($cleaned);
    }

    private function cleanInput($value, $key = null)
    {
        if (empty($value)) return $value;
        
        // Tehlikeli tag'leri kaldır
        $value = preg_replace('/<(script|iframe|object|embed|applet|link|style|meta|base|svg)[^>]*>.*?<\/\1>/is', '', $value);
        $value = preg_replace('/<(script|iframe|object|embed|applet|link|style|meta|base|svg)[^>]*\/?>/is', '', $value);
        
        // Event handler'ları kaldır
        $value = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        
        // Tehlikeli protokoller
        $value = preg_replace('/(javascript|vbscript|data):/i', '', $value);
        
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    private function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->cleanInput($value, $key);
            } elseif (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            }
        }
        return $data;
    }
}