<?php

namespace App\Services\SmsProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TescomProvider implements SmsProviderInterface
{
    protected $credentials;
    protected $apiUrl = 'https://smspanel.tescom.com.tr:9588';

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

     public function sendBulkSms(array $phones, string $message): array
    {
        try {
            $sender = $this->credentials['sender_name'] ?? '';
            $username = $this->credentials['username'] ?? '';
            $password = $this->credentials['password'] ?? '';
            
            $gateway = $this->credentials['gateway'] ?? null;

            if (empty($username) || empty($password)) {
                Log::error('Tescom API bilgileri eksik', [
                    'username_exists' => !empty($username),
                    'password_exists' => !empty($password)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Tescom API kullanıcı adı veya şifre eksik. Lütfen entegrasyon ayarlarınızı kontrol edin.'
                ];
            }

            if (empty($sender)) {
                return [
                    'success' => false,
                    'message' => 'SMS gönderen adı (başlık) tanımlanmamış. Lütfen entegrasyon ayarlarınızı kontrol edin.'
                ];
            }

            $formattedPhones = $this->formatPhoneNumbers($phones);
            
            if (empty($formattedPhones)) {
                return [
                    'success' => false,
                    'message' => 'Geçerli telefon numarası bulunamadı.'
                ];
            }

            $payload = [
                'type' => 1,
                'sendingType' => 1,
                'title' => 'Toplu SMS - ' . date('Y-m-d H:i'),
                'content' => $message,
                'numbers' => $formattedPhones,
                'encoding' => $this->detectEncoding($message),
                'sender' => $sender,
                'validity' => 1440, // 24 saat
                'commercial' => false,
                'skipAhsQuery' => false,
                'recipientType' => 0,
                'customID' => 'bulk_' . uniqid()
            ];

            if (!empty($gateway)) {
                $payload['gateway'] = $gateway;
            }

            if (count($formattedPhones) > 1000) {
                $payload['periodicSettings'] = [
                    'periodType' => 0,
                    'interval' => 1, // 1 dakika aralıklarla
                    'amount' => 1000 // 1000'erli gönder
                ];
            }

            Log::info('Tescom SMS gönderiliyor', [
                'telefon_sayisi' => count($formattedPhones),
                'sender' => $sender,
                'username' => $username
            ]);

            // API isteği gönder
            $response = Http::withBasicAuth($username, $password)
                ->timeout(30)
                ->post($this->apiUrl . '/sms/create', $payload);

            // HTTP durum kodlarına göre kontrol
            if ($response->status() === 401) {
                Log::error('Tescom Authentication Hatası', [
                    'status' => 401,
                    'response' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Kullanıcının bu işlemi yapmaya yetkisi yok.',
                    'error_code' => 'AUTH_FAILED'
                ];
            }

            if ($response->status() === 403) {
                return [
                    'success' => false,
                    'message' => 'Kullanıcı adı veya parola hatalı, ip kısıtı var yada api kullanım yektisi yok',
                    'error_code' => 'FORBIDDEN'
                ];
            }

            if ($response->status() === 400) {
                $result = $response->json();
                $errorMessage = $result['err']['message'] ?? 'Geçersiz istek parametreleri';
                
                return [
                    'success' => false,
                    'message' => 'Tescom API isteği geçersiz: ' . $errorMessage,
                    'error_code' => 'BAD_REQUEST'
                ];
            }

            if ($response->status() === 500 || $response->status() === 502 || $response->status() === 503) {
                return [
                    'success' => false,
                    'message' => 'Tescom SMS servisi şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyin.',
                    'error_code' => 'SERVICE_UNAVAILABLE'
                ];
            }

            $result = $response->json();

            // Başarılı durum kontrolü
            if ($response->successful() && isset($result['data']['pkgID'])) {
                Log::info('Tescom Toplu SMS Başarılı', [
                    'pkgID' => $result['data']['pkgID'],
                    'telefon_sayisi' => count($phones)
                ]);

                return [
                    'success' => true,
                    'message' => count($phones) . ' kişiye SMS başarıyla gönderildi',
                    'response_code' => $result['data']['pkgID'],
                    'package_id' => $result['data']['pkgID']
                ];
            } else {
                // API'den dönen hata mesajlarını kontrol et
                if (isset($result['err'])) {
                    $errorCode = $result['err']['code'] ?? 'UNKNOWN';
                    $errorMessage = $result['err']['message'] ?? 'Bilinmeyen hata';
                    
                    // Özel hata kodları için mesajlar
                    $customErrorMessages = [
                        'INVALID_SENDER' => 'Gönderen adı (başlık) geçersiz. Tescom panelinden onaylı başlıklarınızı kontrol edin.',
                        'INSUFFICIENT_BALANCE' => 'Tescom hesabınızda yeterli bakiye yok.',
                        'INVALID_NUMBER' => 'Geçersiz telefon numarası formatı.',
                        'QUOTA_EXCEEDED' => 'Günlük SMS gönderim limitinizi aştınız.',
                        'INVALID_CREDENTIALS' => 'Tescom API bilgileriniz geçersiz. Lütfen kullanıcı adı ve şifrenizi kontrol edin.'
                    ];
                    
                    $finalMessage = $customErrorMessages[$errorCode] ?? 'SMS gönderilemedi: ' . $errorMessage;
                    
                    Log::error('Tescom SMS API Hatası', [
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'status' => $result['err']['status'] ?? null,
                        'full_response' => $result
                    ]);

                    return [
                        'success' => false,
                        'message' => $finalMessage,
                        'error_code' => $errorCode
                    ];
                }
                
                Log::error('Tescom SMS Beklenmeyen Yanıt', [
                    'response' => $result,
                    'status_code' => $response->status()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'SMS servisi beklenmeyen bir yanıt döndü. Lütfen daha sonra tekrar deneyin.',
                    'error_code' => 'UNEXPECTED_RESPONSE'
                ];
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Tescom SMS Bağlantı Hatası', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Tescom SMS servisine bağlanılamadı. İnternet bağlantınızı kontrol edin veya daha sonra tekrar deneyin.',
                'error_code' => 'CONNECTION_ERROR'
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Tescom SMS Request Hatası', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS isteği gönderilemedi. Lütfen API ayarlarınızı kontrol edin.',
                'error_code' => 'REQUEST_ERROR'
            ];

        } catch (\Exception $e) {
            Log::error('Tescom SMS Genel Hata', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'SMS gönderilirken beklenmeyen bir hata oluştu. Lütfen sistem yöneticisine başvurun.',
                'error_code' => 'GENERAL_ERROR'
            ];
        }
    }

    public function sendSingleSms(string $phone, string $message): array
    {
        return $this->sendBulkSms([$phone], $message);
    }

   
    protected function formatPhoneNumbers(array $phones): array
    {
        $formatted = [];
        
        foreach ($phones as $phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            if (empty($phone)) {
                continue;
            }
            
            if (strlen($phone) == 10 && substr($phone, 0, 1) !== '0') {
                $phone = '0' . $phone;
            }
            
            if (substr($phone, 0, 2) == '90' && strlen($phone) == 12) {
                $phone = '0' . substr($phone, 2);
            }
            
            if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
                $formatted[] = $phone;
            } else {
                Log::warning('Geçersiz telefon numarası formatı', [
                    'original' => $phone,
                    'length' => strlen($phone)
                ]);
            }
        }
        
        return $formatted;
    }

    
    protected function detectEncoding(string $message): int
    {
        if (preg_match('/[ğüşöçıİĞÜŞÖÇ]/u', $message)) {
            return 1; 
        }
        
        if (!mb_check_encoding($message, 'ASCII')) {
            return 2; 
        }
        
        return 0; 
    }

    
}