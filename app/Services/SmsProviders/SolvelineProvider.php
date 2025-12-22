<?php

namespace App\Services\SmsProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolvelineProvider implements SmsProviderInterface
{
    private $username;
    private $password;
    private $sender;
    private $apiUrl = 'https://smslogin.nac.com.tr:9588';

    public function __construct(array $credentials)
    {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->sender = $credentials['sender'] ?? 'SERVISCNTR';
    }

    public function sendSingleSms(string $phone, string $message): array
    {
        try {
            $phone = $this->formatPhone($phone);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withOptions([
                    'verify' => false
                ])
                ->post($this->apiUrl . '/sms/create', [
                    'type' => 1,
                    'sendingType' => 0,
                    'title' => 'smsapi',
                    'content' => $message,
                    'number' => $phone,
                    'encoding' => 1,
                    'sender' => $this->sender
                ]);

            Log::info('Solveline SMS Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']['pkgID'])) {
                    return [
                        'success' => true,
                        'message' => 'SMS başarıyla gönderildi',
                        'response_code' => $data['data']['pkgID'] ?? null,
                        'data' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'SMS gönderilemedi',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Solveline SMS Gönderme Hatası: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'SMS gönderilirken hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    public function sendBulkSms(array $phones, string $message): array
    {
        try {
            $successCount = 0;
            $failCount = 0;
            $results = [];

            foreach ($phones as $phone) {
                $result = $this->sendSingleSms($phone, $message);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                
                $results[] = [
                    'phone' => $phone,
                    'success' => $result['success'],
                    'response' => $result
                ];

                usleep(100000);
            }

            return [
                'success' => $failCount === 0,
                'message' => "$successCount SMS gönderildi, $failCount başarısız",
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'details' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Solveline Toplu SMS Gönderme Hatası: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Toplu SMS gönderilirken hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '9' . $phone;
        }
        
        if (substr($phone, 0, 2) !== '90') {
            $phone = '90' . $phone;
        }
        
        return $phone;
    }

    
}