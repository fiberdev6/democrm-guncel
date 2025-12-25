<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HipcallCallLog;
use App\Models\IntegrationPurchase;
use App\Models\Tenant;
use App\Services\HipcallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HipcallWebhookController extends Controller
{
    public function handle(Request $request, $token)
    {
        try {
            // Token ile entegrasyonu bul
            $integration = IntegrationPurchase::where('webhook_token', $token)
                ->where('is_active', true)
                ->where('status', 'completed')
                ->first();

            if (!$integration) {
                Log::warning('Hipcall: Geçersiz webhook token', [
                    'token' => $token,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook token'
                ], 401);
            }

            $tenant = $integration->tenant;
            
            if (!$tenant) {
                Log::error('Hipcall: Tenant bulunamadı', [
                    'integration_id' => $integration->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            // Webhook verisini logla
            Log::info('Hipcall Webhook - ' . $tenant->firma_adi, [
                'tenant_id' => $tenant->id,
                'event' => $request->input('event'),
                'data' => $request->input('data')
            ]);

            $event = $request->input('event');
            $data = $request->input('data');

            // Event işle
            $this->processWebhook($tenant, $event, $data);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Hipcall Webhook Error:', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    private function processWebhook($tenant, $event, $data)
    {
        switch ($event) {
            case 'call_init':
                $this->handleCallInit($tenant, $data);
                break;
                
            case 'call_hangup':
                $this->handleCallHangup($tenant, $data);
                break;
                
            case 'call_bridged':
                $this->handleCallBridged($tenant, $data);
                break;
        }
    }

    private function handleCallInit($tenant, array $data): void
{
    $callerNumber = $data['caller_number'] ?? null;
    $calleeNumber = $data['callee_number'] ?? null;
    $direction = $data['direction'] ?? 'inbound';
    $callUuid = $data['uuid'] ?? null;
    
    Log::info("Arama başladı - {$tenant->firma_adi}", [
        'tenant_id' => $tenant->id,
        'arayan' => $callerNumber,
        'yön' => $direction,
        'uuid' => $callUuid
    ]);
    
    $customerId = null;
    $customer = null;
    
    if ($direction == 'inbound' && $callerNumber) {
        $customer = $this->findOrCreateCustomer($tenant, $callerNumber, $data);
        $customerId = $customer ? $customer->id : null;
        
        if ($customer && $callUuid) {
            Log::info("call_init'te kart gönderiliyor", [
                'call_uuid' => $callUuid,
                'customer_id' => $customer->id
            ]);
            
            $this->sendCustomerCardToHipcall($tenant, $customer, $callUuid);
        }
    }
    
    HipcallCallLog::create([
        'tenant_id' => $tenant->id,
        'uuid' => $callUuid ?? '',
        'event_type' => 'call_init',
        'caller_number' => $callerNumber,
        'callee_number' => $calleeNumber,
        'direction' => $direction,
        'started_at' => $data['started_at'] ?? now(),
        'customer_id' => $customerId,
        'raw_data' => $data
    ]);
}

    private function handleCallHangup($tenant, $data)
    {
        $uuid = $data['uuid'];
        $duration = $data['call_duration'] ?? 0;
        $recordUrl = $data['record_url'] ?? null;
        $callerNumber = $data['caller_number'] ?? null;
        $direction = $data['direction'] ?? 'inbound';
        
        Log::info("Arama bitti - {$tenant->firma_adi}", [
            'tenant_id' => $tenant->id,
            'uuid' => $uuid,
            'süre' => $duration
        ]);
        
        $customerId = null;
        
        // Gelen arama ise müşteri bul/oluştur
        if ($direction == 'inbound' && $callerNumber) {
            $customer = $this->findOrCreateCustomer($tenant, $callerNumber, $data);
            $customerId = $customer ? $customer->id : null;
        }
        
        // Mevcut kaydı güncelle veya yeni oluştur
        HipcallCallLog::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'uuid' => $uuid
            ],
            [
                'event_type' => 'call_hangup',
                'caller_number' => $data['caller_number'] ?? null,
                'callee_number' => $data['callee_number'] ?? null,
                'call_duration' => $duration,
                'record_url' => $recordUrl,
                'direction' => $direction,
                'started_at' => $data['started_at'] ?? now(),
                'ended_at' => $data['ended_at'] ?? now(),
                'customer_id' => $customerId,
                'raw_data' => $data
            ]
        );
    }

    private function handleCallBridged($tenant, $data)
{
    $callerNumber = $data['caller_number'] ?? null;
    $direction = $data['direction'] ?? 'inbound';
    $callId = $data['uuid'] ?? null; // Call ID olarak uuid kullan
    
    Log::info("Çağrı bağlandı (BRIDGED) - {$tenant->firma_adi}", [
        'tenant_id' => $tenant->id,
        'call_uuid' => $callId,
        'caller' => $callerNumber,
        'direction' => $direction,
        'full_webhook_data' => $data
    ]);
    
    $customerId = null;
    $customer = null;
    
    // Gelen arama ise müşteri bul veya oluştur
    if ($direction == 'inbound' && $callerNumber) {
        $customer = $this->findOrCreateCustomer($tenant, $callerNumber, $data);
        $customerId = $customer ? $customer->id : null;
        
        if ($customer) {
            Log::info("Müşteri bulundu", [
                'customer_id' => $customer->id,
                'customer_name' => $customer->adSoyad,
                'customer_phone' => $customer->tel1
            ]);
        }
        
        if ($customer && $callId) {
            Log::info("Hipcall'a kart gönderiliyor...", [
                'call_uuid' => $callId,
                'customer_id' => $customer->id
            ]);
            
            $this->sendCustomerCardToHipcall($tenant, $customer, $callId);
        } else {
            Log::warning("Kart gönderilemedi", [
                'customer_exists' => $customer ? 'EVET' : 'HAYIR',
                'call_id_exists' => $callId ? 'EVET' : 'HAYIR'
            ]);
        }
    }
    
    // Çağrı kaydını güncelle
    HipcallCallLog::updateOrCreate(
        [
            'tenant_id' => $tenant->id,
            'uuid' => $data['uuid']
        ],
        [
            'event_type' => 'call_bridged',
            'caller_number' => $callerNumber,
            'callee_number' => $data['callee_number'] ?? null,
            'direction' => $direction,
            'started_at' => $data['started_at'] ?? now(),
            'customer_id' => $customerId,
            'raw_data' => $data
        ]
    );
}

private function findOrCreateCustomer($tenant, ?string $phone, array $callData)
{
    if (empty($phone)) {
        return null;
    }
    
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    
    if (str_starts_with($cleanPhone, '90')) {
        $cleanPhone = substr($cleanPhone, 2);
    }
    
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = substr($cleanPhone, 1);
    }
    
    Log::info("Müşteri aranıyor", [
        'original' => $phone,
        'cleaned' => $cleanPhone
    ]);
    
    $customer = Customer::where('firma_id', $tenant->id)
        ->where(function($q) use ($cleanPhone) {
            $q->whereRaw("REPLACE(REPLACE(tel1, ' ', ''), '-', '') LIKE ?", ["%{$cleanPhone}%"])
              ->orWhereRaw("REPLACE(REPLACE(tel2, ' ', ''), '-', '') LIKE ?", ["%{$cleanPhone}%"]);
        })
        ->first();
    
    if ($customer) {
        Log::info("Müşteri bulundu", [
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'name' => $customer->adSoyad ?? 'N/A',
            'db_tel1' => $customer->tel1,
            'searched' => $cleanPhone
        ]);
        return $customer;
    }
    
    Log::info("Müşteri bulunamadı, yeni oluşturuluyor");
    
    try {
        $formattedPhone = substr($cleanPhone, 0, 3) . ' ' . 
                         substr($cleanPhone, 3, 3) . ' ' . 
                         substr($cleanPhone, 6);
        
        $newCustomerId = Customer::insertGetId([
            'firma_id' => $tenant->id,
            'tel1' => $formattedPhone,  // 537 889 6552 formatında
            'adSoyad' => 'Hipcall #' . substr($cleanPhone, -4),
            'not' => 'Hipcall üzerinden otomatik oluşturuldu - ' . now()->format('d.m.Y H:i:s'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $customer = Customer::where('id', $newCustomerId)->first();
        
        Log::info("Yeni müşteri oluşturuldu", [
            'tenant_id' => $tenant->id,
            'customer_id' => $newCustomerId,
            'tel1' => $formattedPhone,
            'hipcall_original' => $phone
        ]);
        
        return $customer;
        
    } catch (\Exception $e) {
        Log::error("Müşteri oluşturulamadı", [
            'tenant_id' => $tenant->id,
            'phone' => $phone,
            'error' => $e->getMessage()
        ]);
        
        return null;
    }
}
    /**
 * Hipcall'a müşteri bilgi kartı gönder
 */
private function sendCustomerCardToHipcall($tenant, $customer, $callUuid)
{
    try {
        Log::info("sendCustomerCardToHipcall başladı");
        
        $hipcallService = new HipcallService($tenant->id);
        
        // Site URL'ini al
        $baseUrl = rtrim(config('app.url'), '/');
        
        Log::info("Kart verisi hazırlanıyor", [
            'base_url' => $baseUrl,
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id
        ]);
        
        // Kart verisini hazırla
        $cardData = $hipcallService->prepareCustomerCard($customer, $tenant->id, $baseUrl);
        
        Log::info("Hipcall API'sine kart gönderiliyor", [
            'call_uuid' => $callUuid,
            'card_data' => $cardData
        ]);
        
        // Hipcall'a gönder
        $result = $hipcallService->sendCard($callUuid, $cardData);
        
        if ($result['success']) {
            Log::info("Kart başarıyla gönderildi!", [
                'call_uuid' => $callUuid,
                'customer_id' => $customer->id
            ]);
        } else {
            Log::error("Kart gönderilemedi", [
                'call_uuid' => $callUuid,
                'error' => $result['message'] ?? 'Bilinmeyen hata',
                'full_response' => $result
            ]);
        }
        
    } catch (\Exception $e) {
        Log::error("Kart gönderme exception", [
            'call_uuid' => $callUuid,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}

public function prepareCustomerCard($customer, $tenantId, $baseUrl)
{
    $customerUrl = "https://democrm.fiberreklam.com/{$tenantId}/musteriler?did={$customer->id}";
    $firma = Tenant::where('id', $tenantId)->first();
    
    Log::info("Kart verisi hazırlanıyor", [
        'customer_id' => $customer->id,
        'customer_name' => $customer->adSoyad,
        'customer_url' => $customerUrl
    ]);
    
    $card = [
        [
            'type' => 'title',
            'text' => 'Serbis CRM',
            'link' => $customerUrl
        ],
        [
            'type' => 'shortText',
            'label' => 'Müşteri',
            'text' => $customer->adSoyad ?? 'Bilinmeyen',
            'link' => $customerUrl
        ]
    ];
    
    if ($firma && !empty($firma->firma_adi)) {
        $card[] = [
            'type' => 'shortText',
            'label' => 'Firma',
            'text' => $firma->firma_adi
        ];
    }
    
    // Telefon
    if (!empty($customer->tel1)) {
        $card[] = [
            'type' => 'shortText',
            'label' => 'Telefon',
            'text' => $customer->tel1
        ];
    }
    
    Log::info("Hazırlanan kart", ['card' => $card]);
    
    return $card;
}
}