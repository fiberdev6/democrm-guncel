<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaytrService
{
    private $merchantId;
    private $merchantKey;
    private $merchantSalt;
    private $testMode;

    public function __construct()
    {
        $this->merchantId = config('paytr.merchant_id');
        $this->merchantKey = config('paytr.merchant_key');
        $this->merchantSalt = config('paytr.merchant_salt');
        $this->testMode = config('paytr.test_mode', '1');
    }

    /**
     * Kullanıcının gerçek IP adresini al
     */
    private function getUserIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // Test modunda varsayılan IP
        return $this->testMode ? '78.190.145.61' : '127.0.0.1';
    }

    /**
     * Ödeme iframeini oluştur
     */
    public function createPaymentIframe($orderData)
    {
        $basket = base64_encode(json_encode($orderData['basket']));
        $payment_amount = intval($orderData['amount'] * 100);
        $user_ip = $this->getUserIP();

        $post_vals = [
            'merchant_id'       => $this->merchantId,
            'user_ip'           => $user_ip,
            'merchant_oid'      => $orderData['order_id'],
            'email'             => $orderData['email'],
            'payment_amount'    => $payment_amount,
            'currency'          => 'TL',
            'test_mode'         => $this->testMode,
            'non_3d'            => '0',
            'merchant_ok_url'   => $orderData['success_url'],
            'merchant_fail_url' => $orderData['fail_url'],
            'user_name'         => $orderData['user_name'],
            'user_address'      => $orderData['user_address'],
            'user_phone'        => $orderData['user_phone'],
            'user_basket'       => $basket,
            'debug_on'          => $this->testMode,
            'client_lang'       => 'tr',
            'no_installment'    => '1',
            'max_installment'   => '0',
            'timeout_limit'     => '30',
        ];

        // Token oluştur
        $hash_str = $this->merchantId .
                    $user_ip .
                    $post_vals['merchant_oid'] .
                    $post_vals['email'] .
                    $payment_amount .
                    $basket .
                    $post_vals['no_installment'] .
                    $post_vals['max_installment'] .
                    $post_vals['currency'] .
                    $post_vals['test_mode'];

        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $this->merchantSalt, $this->merchantKey, true));
        $post_vals['paytr_token'] = $paytr_token;

        Log::info('PayTR Request Data:', [
            'merchant_id' => $this->merchantId,
            'order_id' => $orderData['order_id'],
            'amount' => $payment_amount,
            'user_ip' => $user_ip,
            'hash_string' => $hash_str,
            'token' => $paytr_token
        ]);

        // PAYTR API isteği
        $response = Http::timeout(30)->asForm()->post('https://www.paytr.com/odeme/api/get-token', $post_vals);

        if ($response->successful()) {
            $result = $response->json();

            Log::info('PayTR API Response:', $result);

            if ($result['status'] == 'success') {
                return [
                    'success'    => true,
                    'token'      => $result['token'],
                    'iframe_url' => 'https://www.paytr.com/odeme/guvenli/' . $result['token']
                ];
            } else {
                Log::error('Paytr API Error', $result);
                return [
                    'success' => false,
                    'error'   => $result['reason'] ?? 'Bilinmeyen hata'
                ];
            }
        }

        Log::error('Paytr HTTP Error', [
            'status' => $response->status(),
            'body'   => $response->body()
        ]);

        return [
            'success' => false,
            'error'   => 'Ödeme servisiyle bağlantı kurulamadı'
        ];
    }

    /**
     * Callback doğrulaması - PayTR dokümantasyonuna göre düzeltildi
     */
    public function verifyCallback($postData)
    {
        try {
            // PayTR callback verilerini kontrol et
            $required_fields = ['merchant_oid', 'status', 'total_amount', 'hash'];
            
            foreach ($required_fields as $field) {
                if (!isset($postData[$field])) {
                    Log::error('Missing callback field: ' . $field);
                    return false;
                }
            }

            // Hash oluştur - PayTR dokümantasyonuna göre
            $hash = base64_encode(hash_hmac('sha256', 
                $postData['merchant_oid'] . $this->merchantSalt . $postData['status'] . $postData['total_amount'], 
                $this->merchantKey, true));

            Log::info('Callback Hash Verification:', [
                'calculated_hash' => $hash,
                'received_hash' => $postData['hash'],
                'merchant_oid' => $postData['merchant_oid'],
                'status' => $postData['status'],
                'total_amount' => $postData['total_amount']
            ]);

            $isValid = hash_equals($hash, $postData['hash']);
            
            if (!$isValid) {
                Log::error('Hash verification failed', [
                    'expected' => $hash,
                    'received' => $postData['hash']
                ]);
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('Callback verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sipariş durumu sorgula
     */
    public function checkOrderStatus($orderId)
    {
        $post_vals = [
            'merchant_id' => $this->merchantId,
            'merchant_oid' => $orderId,
        ];

        $paytr_token = base64_encode(hash_hmac('sha256', 
            $post_vals['merchant_id'] . $post_vals['merchant_oid'] . $this->merchantSalt, 
            $this->merchantKey, true));
            
        $post_vals['paytr_token'] = $paytr_token;

        $response = Http::timeout(30)->asForm()->post('https://www.paytr.com/odeme/durum-sorgu', $post_vals);

        if ($response->successful()) {
            $result = $response->json();
            Log::info('Order Status Query Result:', $result);
            return $result;
        }

        Log::error('Order status query failed', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return [
            'status' => 'error',
            'message' => 'Durum sorgulanamadı'
        ];
    }
}