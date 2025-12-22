<?php

namespace App\Services;

use App\Models\IntegrationPurchase;
use App\Models\HipcallCallLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HipcallService
{
    protected $apiKey;
    protected $baseUrl = 'https://use.hipcall.com.tr/api/v3';
    
    public function __construct($tenantId)
    {
        $purchase = IntegrationPurchase::where('tenant_id', $tenantId)
            ->whereHas('integration', function($q) {
                $q->where('slug', 'hipcall');
            })
            ->where('status', 'completed')
            ->where('is_active', true)
            ->first();
        
        if ($purchase && $purchase->credentials) {
            $credentials = is_string($purchase->credentials) 
                ? json_decode($purchase->credentials, true) 
                : $purchase->credentials;
            
            $this->apiKey = $credentials['api_key'] ?? null;
        }
    }
   
    public function getCalls($limit = 20)
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'API Key eksik. Lütfen entegrasyon ayarlarından API Key girin.'
            ];
        }
        
        try {
            Log::info('Hipcall API Request', [
                'url' => $this->baseUrl . '/calls',
                'api_key_exists' => !empty($this->apiKey)
            ]);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/calls', [
                    'limit' => $limit,
                    'sort' => 'started_at.desc'
                ]);
            
            Log::info('Hipcall API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'data' => $data,
                    'calls' => $data['data'] ?? $data['calls'] ?? $data ?? []
                ];
            }
            
            // Hata durumu
            return [
                'success' => false,
                'message' => 'Hipcall API yanıt vermedi',
                'status_code' => $response->status(),
                'error' => $response->body()
            ];
            
        } catch (\Exception $e) {
            Log::error('Hipcall API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

public function getAllContacts()
{
    $allContacts = [];
    $page = 1;
    $perPage = 50;
    $maxPages = 20;
    
    do {
        Log::info("Hipcall contacts sayfa {$page} çekiliyor...");
        
        // Önce listeyi çek
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->get($this->baseUrl . '/contacts', [
                'page' => $page,
                'per_page' => $perPage
            ]);
        
        if (!$response->successful()) {
            break;
        }
        
        $data = $response->json();
        $contactsList = $data['data'] ?? $data['contacts'] ?? [];
        
        // Her contact için detayını çek
        foreach ($contactsList as $contact) {
            $contactId = $contact['id'] ?? null;
            
            if ($contactId) {
                // Detay çek: /api/v3/contacts/{id}
                $detailResponse = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($this->baseUrl . "/contacts/{$contactId}");
                
                if ($detailResponse->successful()) {
                    $detailData = $detailResponse->json();
                    // API response: {"data": {...}}
                    $fullContact = $detailData['data'] ?? $detailData;
                    
                    // Telefon numarasını phones array'inden çıkar
                    if (isset($fullContact['phones']) && is_array($fullContact['phones']) && count($fullContact['phones']) > 0) {
                        $fullContact['phone'] = $fullContact['phones'][0]['number'] ?? null;
                    }
                    
                    // Email'i emails array'inden çıkar
                    if (isset($fullContact['emails']) && is_array($fullContact['emails']) && count($fullContact['emails']) > 0) {
                        $fullContact['email'] = $fullContact['emails'][0]['email'] ?? null;
                    }
                    
                    // Company name'i çıkar
                    if (isset($fullContact['company']['name'])) {
                        $fullContact['company_name'] = $fullContact['company']['name'];
                    }
                    
                    $allContacts[] = $fullContact;
                } else {
                    $allContacts[] = $contact;
                }
                
                usleep(150000); // 150ms
            } else {
                $allContacts[] = $contact;
            }
        }
        
        Log::info("Sayfa {$page}: " . count($contactsList) . " kişi çekildi. Toplam: " . count($allContacts));
        
        $hasMore = count($contactsList) >= $perPage;
        $page++;
        
        if ($page > $maxPages) {
            break;
        }
        
        if ($hasMore) {
            sleep(1);
        }
        
    } while ($hasMore);
    
    return [
        'success' => true,
        'contacts' => $allContacts,
        'total' => count($allContacts)
    ];
}

/**
 * Hipcall'a bilgilendirme kartı gönder
 */
public function sendCard($callId, $cardData)
{
    if (!$this->apiKey) {
        return [
            'success' => false,
            'message' => 'API Key eksik'
        ];
    }
    
    try {
        $url = $this->baseUrl . "/calls/{$callId}/cards";
        
        Log::info('Hipcall Card Gönderiliyor', [
            'url' => $url,
            'call_id' => $callId,
            'card_data' => $cardData
        ]);
        
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'card' => $cardData 
            ]);
        
        Log::info('Hipcall Card Response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'headers' => $response->headers()
        ]);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Kart başarıyla gönderildi',
                'data' => $response->json()
            ];
        }
        
        return [
            'success' => false,
            'message' => "Kart gönderilemedi (HTTP {$response->status()})",
            'status' => $response->status(),
            'error' => $response->body()
        ];
        
    } catch (\Exception $e) {
        Log::error('Hipcall sendCard exception', [
            'call_id' => $callId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'message' => 'Kart gönderme hatası: ' . $e->getMessage()
        ];
    }
}

/**
 * Müşteri için kart verisi hazırla
 */
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
            'link' => $customerUrl,
            'text' => 'Serbis CRM',
            'type' => 'title'
        ],
        [
            'label' => 'Müşteri Adı',
            'link' => $customerUrl,
            'text' => ($customer->adSoyad ?? 'Bilinmeyen') ,
            'type' => 'shortText',
            
        ]
    ];
    
    if (!empty($firma->name)) {
        $card[] = [
            'type' => 'shortText',
            'label' => 'Firma',
            'text' => $firma->name,
        ];
    }
    
    if (!empty($customer->tel1)) {
        $card[] = [
            'type' => 'shortText',
            'label' => 'Telefon',
            'text' => $customer->tel1,
        ];
    }
    
     Log::info("Hazırlanan kart", ['card' => $card]);
    
    return $card;
}

/**
 * Hipcall rehberine yeni kişi ekle
 */
public function createContact($contactData)
{
    if (!$this->apiKey) {
        return [
            'success' => false,
            'message' => 'API Key eksik'
        ];
    }
    
    try {
        $nameParts = explode(' ', $contactData['name'], 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        $phone = preg_replace('/[^0-9]/', '', $contactData['phone']);
        
        if (!str_starts_with($phone, '90') && strlen($phone) == 10) {
            $phone = '90' . $phone;
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        $requestData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phones' => [
                [
                    'country' => 'TR',
                    'number' => $phone
                ]
            ]
        ];
        
        if (!empty($contactData['phone2'])) {
            $phone2 = preg_replace('/[^0-9]/', '', $contactData['phone2']);
            if (!str_starts_with($phone2, '90') && strlen($phone2) == 10) {
                $phone2 = '90' . $phone2;
            }
            if (!str_starts_with($phone2, '+')) {
                $phone2 = '+' . $phone2;
            }
            
            $requestData['phones'][] = [
                'country' => 'TR',
                'number' => $phone2
            ];
        }
        
        if (!empty($contactData['tc_no']) || !empty($contactData['vergi_no'])) {
            $customNote = [];
            if (!empty($contactData['tc_no'])) {
                $customNote[] = "TC: " . $contactData['tc_no'];
            }
            if (!empty($contactData['vergi_no'])) {
                $customNote[] = "VN: " . $contactData['vergi_no'];
                if (!empty($contactData['vergi_dairesi'])) {
                    $customNote[] = "VD: " . $contactData['vergi_dairesi'];
                }
            }
            $requestData['custom_url'] = implode(' | ', $customNote);
        }
        
        Log::info('Hipcall Create Contact Request', [
            'url' => $this->baseUrl . '/contacts',
            'data' => $requestData
        ]);
        
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/contacts', $requestData);
        
        Log::info('Hipcall Create Contact Response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Kişi Hipcall rehberine eklendi',
                'data' => $response->json()
            ];
        }
        
        return [
            'success' => false,
            'message' => "Hipcall rehberine eklenemedi (HTTP {$response->status()})",
            'error' => $response->body()
        ];
        
    } catch (\Exception $e) {
        Log::error('Hipcall createContact error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ];
    }
}
    
}