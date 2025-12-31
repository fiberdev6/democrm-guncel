<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Temel kurallar (her ikisi için de geçerli)
        $rules = [
            'name' => 'required|string|max:100',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'il' => 'nullable|integer',
            'ilce' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
            'tcno' => 'nullable|string|max:11',
            'vergiNo' => 'nullable|string|max:10',
            'vergiDairesi' => 'nullable|string|max:100',
            'mTipi' => 'required|in:1,2',
        ];

        // Update için ek kurallar (opsiyonel)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('id')) {
            $rules['kayitTarihi'] = 'nullable|date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Müşteri adı zorunludur.',
            'name.max' => 'Müşteri adı en fazla :max karakter olabilir.',
            'tel1.required' => 'Telefon numarası zorunludur.',
            'tel1.max' => 'Telefon en fazla :max karakter olabilir.',
            'address.max' => 'Adres en fazla :max karakter olabilir.',
            'tcno.max' => 'TC Kimlik No 11 haneli olmalıdır.',
            'vergiNo.max' => 'Vergi No 10 haneli olmalıdır.',
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
     * XSS temizliği
     */
    protected function prepareForValidation(): void
    {
        $cleaned = [];
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = $this->cleanInput($value);
            } elseif (is_array($value)) {
                $cleaned[$key] = $this->cleanArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        $this->merge($cleaned);
    }

    private function cleanInput($value)
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
                $data[$key] = $this->cleanInput($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            }
        }
        return $data;
    }
}
