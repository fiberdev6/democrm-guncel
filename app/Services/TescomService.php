<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TescomService
{
    protected $hostname;
    protected $username;
    protected $password;
    protected $sender;

    public function __construct()
    {
        $this->hostname = config('sms.tescom.hostname');
        $this->username = config('sms.tescom.username');
        $this->password = config('sms.tescom.password');
        $this->sender = config('sms.tescom.sender', 'SERBISYS');
    }

    /**
     * Tescom API üzerinden SMS gönderir
     *
     * @param string $phoneNumber Telefon numarası (5xxxxxxxxx formatında)
     * @param string $message Gönderilecek mesaj
     * @return array
     */
    public function sendSms($phoneNumber, $message)
{
    try {
        $cleanPhone = str_replace(' ', '', $phoneNumber);
        
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = substr($cleanPhone, 1);
        }
        
        if (substr($cleanPhone, 0, 2) !== '90') {
            $cleanPhone = '90' . $cleanPhone;
        }

        // Tescom REST API endpoint'i
        $apiUrl = "https://{$this->hostname}:9588/sms/create";

        $response = Http::withBasicAuth($this->username, $this->password)
            ->timeout(30)
            ->post($apiUrl, [
                'type' => 1,            
                'sendingType' => 0,      
                'title' => 'Dogrulama',
                'content' => $message,
                'number' => $cleanPhone,
                'encoding' => 0,
                'sender' => $this->sender,
            ]);

        Log::info('Tescom SMS Gönderim Denemesi', [
            'phone' => $cleanPhone,
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            
            // Tescom API başarılı yanıt kontrolü
            if (array_key_exists('err', $responseData) && $responseData['err'] === null) {                return [
                    'success' => true,
                    'message' => 'SMS başarıyla gönderildi',
                    'message_id' => $responseData['data']['pkgID'] ?? null,
                    'response' => $responseData
                ];
            }
            
            // Hata varsa
            if (array_key_exists('err', $responseData) && $responseData['err'] !== null) {
                $error = $responseData['err'];
                return [
                    'success' => false,
                    'message' => $error['message'] ?? 'SMS gönderilemedi',
                    'error_code' => $error['code'] ?? null,
                    'error_status' => $error['status'] ?? null,
                    'response' => $responseData
                ];
            }
            
            return [
                'success' => false,
                'message' => 'SMS gönderilemedi',
                'response' => $responseData
            ];
        }

        return [
            'success' => false,
            'message' => 'SMS servisi ile bağlantı kurulamadı',
            'error' => $response->body(),
            'status_code' => $response->status()
        ];

    } catch (\Exception $e) {
        Log::error('Tescom SMS Gönderim Hatası', [
            'phone' => $phoneNumber,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'message' => 'SMS gönderiminde hata oluştu: ' . $e->getMessage()
        ];
    }
}

    /**
     * Doğrulama kodu SMS'i gönderir
     *
     * @param string $phoneNumber
     * @param string $code
     * @return array
     */
    public function sendVerificationCode($phoneNumber, $code)
    {
        $message = "Serbis CRM dogrulama kodunuz: {$code}\n\nBu kodu kimseyle paylasmayin.";
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Genel bilgilendirme SMS'i gönderir
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendNotification($phoneNumber, $message)
    {
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * İleri tarihli SMS gönderir (opsiyonel)
     *
     * @param string $phoneNumber
     * @param string $message
     * @param string $sendingDate Format: Y-m-d H:i
     * @return array
     */
    public function sendScheduledSms($phoneNumber, $message, $sendingDate)
    {
        try {
            $cleanPhone = str_replace(' ', '', $phoneNumber);
            
            if (substr($cleanPhone, 0, 1) === '0') {
                $cleanPhone = substr($cleanPhone, 1);
            }
            
            if (substr($cleanPhone, 0, 2) !== '90') {
                $cleanPhone = '90' . $cleanPhone;
            }

            $apiUrl = "https://{$this->hostname}:9588/sms/create";

            $response = Http::withBasicAuth($this->username, $this->password)
            ->timeout(30)
            ->post($apiUrl, [
                'type' => 1,             
                'sendingType' => 0,       
                'title' => 'Bilgilendirme',
                'content' => $message,
                'number' => $cleanPhone,
                'encoding' => 0,
                'sender' => $this->sender,
                'sendingDate' => $sendingDate,
            ]);

            Log::info('Tescom Scheduled SMS Gönderim', [
                'phone' => $cleanPhone,
                'scheduled_date' => $sendingDate,
                'status' => $response->status(),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (array_key_exists('err', $responseData) && $responseData['err'] === null) {
                    return [
                        'success' => true,
                        'message' => 'Zamanlanmış SMS başarıyla kaydedildi',
                        'message_id' => $responseData['data']['pkgID'] ?? null,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Zamanlanmış SMS gönderilemedi'
            ];

        } catch (\Exception $e) {
            Log::error('Tescom Scheduled SMS Hatası', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS zamanlaması sırasında hata oluştu'
            ];
        }
    }
}