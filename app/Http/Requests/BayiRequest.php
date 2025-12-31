<?php
// app/Http/Requests/BayiRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BayiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|min:2|max:100',
            'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'tel' => 'required|string|max:20',
            'baslamaTarihi' => 'required|date',
            'il' => 'nullable|integer',
            'ilce' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
            'vergiNo' => 'nullable|string|max:10',
            'vergiDairesi' => 'nullable|string|max:100',
            'belgePdf' => 'nullable|array|max:2',
            'belgePdf.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,svg|max:5120',
        ];

        // Store için şifre zorunlu, Update için opsiyonel
        if ($this->isMethod('POST') && !$this->route('id')) {
            $rules['password'] = 'required|string|min:6|max:50';
        } else {
            $rules['password'] = 'nullable|string|min:6|max:50';
            $rules['status'] = 'required|in:0,1';
            $rules['ayrilmaTarihi'] = 'nullable|date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bayi adı zorunludur.',
            'name.min' => 'Bayi adı en az :min karakter olmalıdır.',
            'name.max' => 'Bayi adı en fazla :max karakter olabilir.',
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.min' => 'Kullanıcı adı en az :min karakter olmalıdır.',
            'username.max' => 'Kullanıcı adı en fazla :max karakter olabilir.',
            'username.regex' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
            'tel.required' => 'Telefon numarası zorunludur.',
            'tel.max' => 'Telefon numarası en fazla :max karakter olabilir.',
            'baslamaTarihi.required' => 'Başlama tarihi zorunludur.',
            'baslamaTarihi.date' => 'Geçerli bir tarih giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
            'address.max' => 'Adres en fazla :max karakter olabilir.',
            'vergiNo.max' => 'Vergi numarası en fazla :max karakter olabilir.',
            'vergiDairesi.max' => 'Vergi dairesi en fazla :max karakter olabilir.',
            'status.required' => 'Bayi durumu seçiniz.',
            'ayrilmaTarihi.date' => 'Geçerli bir tarih giriniz.',
            'belgePdf.max' => 'En fazla 2 belge yükleyebilirsiniz.',
            'belgePdf.*.mimes' => 'Sadece PDF, JPG, JPEG, PNG ve SVG dosyaları kabul edilir.',
            'belgePdf.*.max' => 'Dosya boyutu en fazla 5MB olabilir.',
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
            // Dosya alanlarını atla
            if ($key === 'belgePdf') {
                continue;
            }
            
            if (is_string($value)) {
                $cleaned[$key] = $this->cleanInput($value, $key);
            } elseif (is_array($value)) {
                $cleaned[$key] = $this->cleanArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        $this->merge($cleaned);
    }

    private function cleanInput($value, $key = null)
    {
        if (empty($value)) return $value;
        
        // Şifre alanını temizleme
        if ($key === 'password') {
            return $value;
        }
        
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