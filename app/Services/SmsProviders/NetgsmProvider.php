<?php

namespace App\Services\SmsProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetgsmProvider implements SmsProviderInterface
{
    protected $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function sendBulkSms(array $phones, string $message): array
    {
        try {
            $username = $this->credentials['username'] ?? '';
            $password = $this->credentials['password'] ?? '';
            $sender = $this->credentials['sender_name'] ?? '';

            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'SMS API bilgileri eksik'
                ];
            }

            $phoneList = implode(',', $phones);

            $response = Http::get('https://api.netgsm.com.tr/sms/send/get', [
                'usercode' => $username,
                'password' => $password,
                'gsmno' => $phoneList,
                'message' => $message,
                'msgheader' => $sender,
                'startdate' => '',
                'stopdate' => ''
            ]);

            $result = trim($response->body());

            if (substr($result, 0, 2) == '00' || substr($result, 0, 2) == '01' || substr($result, 0, 2) == '02') {
                Log::info('Netgsm Toplu SMS Başarılı', [
                    'telefon_sayisi' => count($phones),
                    'response' => $result
                ]);

                return [
                    'success' => true,
                    'message' => count($phones) . ' kişiye SMS başarıyla gönderildi',
                    'response_code' => $result
                ];
            } else {
                Log::error('Netgsm SMS Hatası', [
                    'response' => $result,
                    'phones' => $phones
                ]);

                return [
                    'success' => false,
                    'message' => 'SMS gönderilemedi. Hata kodu: ' . $result
                ];
            }

        } catch (\Exception $e) {
            Log::error('Netgsm SMS Exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS gönderilirken bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    public function sendSingleSms(string $phone, string $message): array
    {
        return $this->sendBulkSms([$phone], $message);
    }
}