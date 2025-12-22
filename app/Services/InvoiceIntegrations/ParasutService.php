<?php

namespace App\Services\InvoiceIntegrations;

use App\Contracts\InvoiceIntegrationInterface;
use App\Models\IntegrationPurchase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ParasutService implements InvoiceIntegrationInterface
{
    protected $credentials;
    protected $accessToken;
    protected $refreshToken;
    protected $companyId;
    protected $baseUrl;
    protected $redirectUri;
    protected $tenantId;

    public function __construct(array $credentials, ?int $tenantId = null)
    {
        $this->credentials = $credentials;
        $this->companyId = $credentials['company_id'] ?? null;
        $this->baseUrl = 'https://api.heroku-staging.parasut.com';
        $this->redirectUri = $credentials['redirect_uri'] ?? 'urn:ietf:wg:oauth:2.0:oob';
        $this->tenantId = $tenantId;
        
        if (!$this->companyId) {
            throw new Exception('Company ID eksik');
        }
        
        // Access token al
        $this->getAccessToken();
    }

    /**
     * OAuth2 Access Token al
     */
    protected function getAccessToken()
    {
        try {
            $cacheKey = 'parasut_token_' . $this->tenantId . '_' . $this->companyId;
            
            $cachedToken = Cache::get($cacheKey);
            if ($cachedToken) {
                $this->accessToken = $cachedToken['access_token'];
                $this->refreshToken = $cachedToken['refresh_token'] ?? null;
                Log::info('Paraşüt token cache\'den alındı');
                return true;
            }

            if (!empty($this->credentials['refresh_token'])) {
                if ($this->refreshAccessToken()) {
                    return true;
                }
            }

            Log::info('Password grant ile yeni token alınıyor');
            
            $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => $this->credentials['client_id'],
                'client_secret' => $this->credentials['client_secret'],
                'username' => $this->credentials['username'],
                'password' => $this->credentials['password'],
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                $this->refreshToken = $data['refresh_token'];
                
                Cache::put($cacheKey, [
                    'access_token' => $this->accessToken,
                    'refresh_token' => $this->refreshToken,
                ], now()->addMinutes(90));
                
                $this->saveRefreshToken($this->refreshToken);
                
                Log::info('Paraşüt token başarıyla alındı');
                return true;
            }
            
            throw new Exception('Token alınamadı: ' . $response->body());
            
        } catch (Exception $e) {
            Log::error('Paraşüt token hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function refreshAccessToken(): bool
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => $this->credentials['client_id'],
                'client_secret' => $this->credentials['client_secret'],
                'refresh_token' => $this->credentials['refresh_token'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                $this->refreshToken = $data['refresh_token'];
                
                $cacheKey = 'parasut_token_' . $this->tenantId . '_' . $this->companyId;
                Cache::put($cacheKey, [
                    'access_token' => $this->accessToken,
                    'refresh_token' => $this->refreshToken,
                ], now()->addMinutes(90));
                
                $this->saveRefreshToken($this->refreshToken);
                Log::info('Token refresh edildi');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::warning('Refresh token hatası: ' . $e->getMessage());
            return false;
        }
    }

    protected function saveRefreshToken(string $refreshToken)
    {
        if (!$this->tenantId) {
            return;
        }

        try {
            $integration = IntegrationPurchase::where('tenant_id', $this->tenantId)
                ->whereHas('integration', function($q) {
                    $q->where('slug', 'parasut');
                })
                ->first();

            if ($integration) {
                $credentials = $integration->credentials;
                $credentials['refresh_token'] = $refreshToken;
                $integration->update(['credentials' => $credentials]);
            }
        } catch (Exception $e) {
            Log::error('Refresh token kaydetme hatası: ' . $e->getMessage());
        }
    }

    protected function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $url = "{$this->baseUrl}/v4/{$this->companyId}/{$endpoint}";
        
        Log::info('Paraşüt API isteği', [
            'method' => $method,
            'url' => $url
        ]);

        try {
            $response = Http::withToken($this->accessToken)
                ->accept('application/json')
                ->contentType('application/json');

            if ($method === 'get' && !empty($data)) {
                $response = $response->get($url, $data);
            } else {
                $response = $response->$method($url, $data);
            }

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 401) {
                Log::warning('Token geçersiz, yenileniyor');
                if ($this->refreshAccessToken()) {
                    return $this->makeRequest($method, $endpoint, $data);
                }
            }

            throw new Exception('API Hatası: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Paraşüt API Hatası', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Müşteri senkronize et
     */
    public function syncCustomer(array $customerData): array
{
    try {
        // Önce müşteriyi ara
        $existingCustomer = $this->findCustomer($customerData);
        
        if ($existingCustomer) {
            Log::info('Müşteri Paraşüt\'te zaten mevcut', [
                'customer_id' => $existingCustomer['id'],
                'name' => $existingCustomer['attributes']['name'] ?? 'N/A'
            ]);
            
            return [
                'success' => true,
                'customer_id' => $existingCustomer['id'],
                'action' => 'found',
                'message' => 'Müşteri zaten Paraşüt\'te kayıtlı'
            ];
        }

        Log::info('Yeni müşteri Paraşüt\'e ekleniyor', ['name' => $customerData['adSoyad']]);
        
        $parasutData = [
            'data' => [
                'type' => 'contacts',
                'attributes' => [
                    'email' => $customerData['email'] ?? null,
                    'name' => $customerData['adSoyad'],
                    'contact_type' => $customerData['musteriTipi'] == '1' ? 'person' : 'company',
                    'tax_number' => $customerData['vergiNo'] ?? $customerData['tcNo'] ?? null,
                    'tax_office' => $customerData['vergiDairesi'] ?? null,
                    'account_type' => 'customer',
                    'address' => $customerData['adres'] ?? null,
                    'city' => $customerData['il'] ?? null,
                    'district' => $customerData['ilce'] ?? null,
                    'phone' => $customerData['tel1'] ?? null,
                ]
            ]
        ];

        $response = $this->makeRequest('post', 'contacts', $parasutData);

        Log::info('Müşteri Paraşüt\'e eklendi', ['customer_id' => $response['data']['id']]);

        return [
            'success' => true,
            'customer_id' => $response['data']['id'],
            'action' => 'created',
            'message' => 'Müşteri Paraşüt\'e yeni eklendi'
        ];

    } catch (Exception $e) {
        Log::error('Paraşüt müşteri senkronizasyonu hatası: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Müşteri ara - Geliştirilmiş versiyon
 */
protected function findCustomer(array $customerData)
{
    try {
        $searchCriteria = [];
        
        // 1. Vergi numarası ile ara (Kurumsal müşteriler için)
        if (!empty($customerData['vergiNo'])) {
            Log::info('Vergi numarası ile müşteri aranıyor', ['vergiNo' => $customerData['vergiNo']]);
            
            $response = $this->makeRequest('get', 'contacts', [
                'filter[tax_number]' => $customerData['vergiNo']
            ]);

            if (!empty($response['data']) && count($response['data']) > 0) {
                Log::info('Müşteri vergi numarası ile bulundu', [
                    'customer_id' => $response['data'][0]['id']
                ]);
                return $response['data'][0];
            }
        }

        // 2. TC No ile ara (Bireysel müşteriler için)
        if (!empty($customerData['tcNo'])) {
            Log::info('TC No ile müşteri aranıyor', ['tcNo' => $customerData['tcNo']]);
            
            $response = $this->makeRequest('get', 'contacts', [
                'filter[tax_number]' => $customerData['tcNo']
            ]);

            if (!empty($response['data']) && count($response['data']) > 0) {
                Log::info('Müşteri TC No ile bulundu', [
                    'customer_id' => $response['data'][0]['id']
                ]);
                return $response['data'][0];
            }
        }

        // 3. İsim ve telefon ile ara (son şans)
        if (!empty($customerData['adSoyad']) && !empty($customerData['tel1'])) {
            Log::info('İsim ile müşteri aranıyor', ['name' => $customerData['adSoyad']]);
            
            $response = $this->makeRequest('get', 'contacts', [
                'filter[name]' => $customerData['adSoyad']
            ]);

            if (!empty($response['data'])) {
                // Telefon numarası eşleşmesi kontrolü
                foreach ($response['data'] as $customer) {
                    $phone = $customer['attributes']['phone'] ?? '';
                    // Telefon numarasını temizle (boşluk, tire vb. kaldır)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                    $cleanCustomerPhone = preg_replace('/[^0-9]/', '', $customerData['tel1']);
                    
                    if ($cleanPhone === $cleanCustomerPhone) {
                        Log::info('Müşteri isim ve telefon ile bulundu', [
                            'customer_id' => $customer['id']
                        ]);
                        return $customer;
                    }
                }
            }
        }

        Log::info('Müşteri Paraşüt\'te bulunamadı, yeni müşteri olarak eklenecek');
        return null;
        
    } catch (Exception $e) {
        Log::warning('Müşteri arama hatası: ' . $e->getMessage());
        return null;
    }
}
public function updateCustomer(string $contactId, array $customerData): array
{
    try {
        Log::info('Müşteri Paraşüt\'te güncelleniyor', [
            'contact_id' => $contactId,
            'name' => $customerData['adSoyad']
        ]);
        
        $parasutData = [
            'data' => [
                'id' => $contactId,
                'type' => 'contacts',
                'attributes' => [
                    'email' => $customerData['email'] ?? null,
                    'name' => $customerData['adSoyad'],
                    'contact_type' => $customerData['musteriTipi'] == '1' ? 'person' : 'company',
                    'tax_number' => $customerData['vergiNo'] ?? $customerData['tcNo'] ?? null,
                    'tax_office' => $customerData['vergiDairesi'] ?? null,
                    'address' => $customerData['adres'] ?? null,
                    'city' => $customerData['il'] ?? null,
                    'district' => $customerData['ilce'] ?? null,
                    'phone' => $customerData['tel1'] ?? null,
                ]
            ]
        ];

        $response = $this->makeRequest('put', "contacts/{$contactId}", $parasutData);

        Log::info('Müşteri Paraşüt\'te güncellendi', ['contact_id' => $contactId]);

        return [
            'success' => true,
            'customer_id' => $contactId,
            'action' => 'updated',
            'message' => 'Müşteri Paraşüt\'te güncellendi'
        ];

    } catch (Exception $e) {
        Log::error('Paraşüt müşteri güncelleme hatası: ' . $e->getMessage(), [
            'contact_id' => $contactId
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
    /**
     * ✅ Ürünleri Paraşüt'e ekle (ESKİ KOD GİBİ)
     */
    protected function syncProducts(array $items): array
    {
        $syncedProducts = [];
        
        foreach ($items as $item) {
            try {
                // Önce ürünü ara
                $existingProduct = $this->findProduct($item['aciklama']);
                
                if ($existingProduct) {
                    $syncedProducts[] = $existingProduct['id'];
                    Log::info('Ürün bulundu', [
                        'product_id' => $existingProduct['id'],
                        'name' => $item['aciklama']
                    ]);
                    continue;
                }

                // Ürün yoksa oluştur
                $productData = [
                    'data' => [
                        'type' => 'products',
                        'attributes' => [
                            'code' => '', // Boş bırakabilirsiniz
                            'name' => $item['aciklama'],
                            'vat_rate' => 20, // KDV oranı
                        ]
                    ]
                ];

                $response = $this->makeRequest('post', 'products', $productData);
                $productId = $response['data']['id'];
                
                $syncedProducts[] = $productId;
                
                Log::info('Ürün oluşturuldu', [
                    'product_id' => $productId,
                    'name' => $item['aciklama']
                ]);

                // API rate limit için kısa bekleme
                usleep(200000); // 0.2 saniye
                
            } catch (Exception $e) {
                Log::error('Ürün senkronizasyon hatası', [
                    'product_name' => $item['aciklama'],
                    'error' => $e->getMessage()
                ]);
                // Ürün eklenemese bile devam et
                $syncedProducts[] = null;
            }
        }

        return $syncedProducts;
    }

    /**
     * Ürün ara
     */
    protected function findProduct(string $productName)
    {
        try {
            $response = $this->makeRequest('get', 'products', [
                'filter[name]' => $productName
            ]);

            if (!empty($response['data'])) {
                return $response['data'][0];
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * ✅ Fatura oluştur (ESKİ KOD MANTIĞI)
     */
    public function createInvoice(array $invoiceData): array
{
    try {
        Log::info('Fatura oluşturuluyor', [
            'invoice_number' => $invoiceData['faturaNumarasi'] ?? null
        ]);

        // 1. Müşteriyi senkronize et
        $customerSync = $this->syncCustomer($invoiceData['customer']);
        
        if (!$customerSync['success']) {
            throw new Exception('Müşteri senkronizasyonu başarısız: ' . ($customerSync['error'] ?? 'Bilinmeyen hata'));
        }

        // Müşteri durumunu logla
        Log::info('Müşteri durumu', [
            'customer_id' => $customerSync['customer_id'],
            'action' => $customerSync['action'],
            'message' => $customerSync['message'] ?? ''
        ]);

        // 2. Ürünleri senkronize et
        $productIds = $this->syncProducts($invoiceData['items']);

        // 3. Fatura detaylarını hazırla
        $details = [];
        foreach ($invoiceData['items'] as $index => $item) {
            $detail = [
                'type' => 'sales_invoice_details',
                'attributes' => [
                    'quantity' => (float) $item['miktar'],
                    'unit_price' => (float) $item['fiyat'],
                    'vat_rate' => (float) ($invoiceData['kdvTutar'] ?? 20),
                    'description' => $item['aciklama'] ?? '',
                ]
            ];

             if (!empty($invoiceData['tevkifatKodu'])) {
        $detail['attributes']['vat_withholding_code'] = $invoiceData['tevkifatKodu'];
    }
    
    // ✅ KDV istisna kodu varsa ekle (sadece KDV %0 veya indirimli durumlarda)
    if (!empty($invoiceData['kdvKodu'])) {
        $detail['attributes']['vat_exemption_reason_code'] = $invoiceData['kdvKodu'];
        
        if (!empty($invoiceData['kdvAciklama'])) {
            $detail['attributes']['vat_exemption_reason'] = $invoiceData['kdvAciklama'];
        }
    }

            // Ürün ID'sini ekle
            if (!empty($productIds[$index])) {
                $detail['relationships'] = [
                    'product' => [
                        'data' => [
                            'id' => $productIds[$index],
                            'type' => 'products'
                        ]
                    ]
                ];
            }

            $details[] = $detail;
        }

        // 4. Tevkifat oranını hesapla (Paraşüt formatına uygun)
        $withholding_rate = 0;
        if (!empty($invoiceData['vat_withholding_rate']) && $invoiceData['vat_withholding_rate'] > 0) {
            // Örn: 2/10 = %20, 3/10 = %30, 5/10 = %50
            $withholding_rate = ($invoiceData['vat_withholding_rate'] * 10);
        }

        // 5. Faturayı oluştur
        $parasutInvoiceData = [
            'data' => [
                'type' => 'sales_invoices',
                'attributes' => [
                    'item_type' => 'invoice',
                    'description' => $invoiceData['faturaAciklama'] ?? ($invoiceData['faturaNumarasi'] ?? 'Servis Faturası'),
                    'issue_date' => $invoiceData['faturaTarihi'],
                    'due_date' => $invoiceData['faturaTarihi'],
                    'currency' => 'TRL',
                    'withholding_rate' => 0, // Stopaj oranı (genelde 0)
                    'vat_withholding_rate' => $withholding_rate,
                    'invoice_discount_type' => 'amount',
                    'invoice_discount' => (float) ($invoiceData['indirim'] ?? 0),
                ],
                'relationships' => [
                    'contact' => [
                        'data' => [
                            'type' => 'contacts',
                            'id' => $customerSync['customer_id']
                        ]
                    ],
                    'details' => [
                        'data' => $details
                    ]
                ]
            ]
        ];

        Log::info('Paraşüt API\'ye fatura gönderiliyor', [
            'customer_id' => $customerSync['customer_id'],
            'items_count' => count($details),
            'total' => $invoiceData['genelToplam'] ?? 0
        ]);

        $response = $this->makeRequest('post', 'sales_invoices', $parasutInvoiceData);

        $invoiceId = $response['data']['id'];
        
        Log::info('Fatura Paraşüt\'te oluşturuldu', [
            'invoice_id' => $invoiceId,
            'invoice_no' => $response['data']['attributes']['invoice_no'] ?? null
        ]);
       

        return [
            'success' => true,
            'invoice_id' => $invoiceId,
            'invoice_number' => $response['data']['attributes']['invoice_no'] ?? null,
            'customer_action' => $customerSync['action'], // 'found' veya 'created'
            'data' => $response['data']
        ];

    } catch (Exception $e) {
        Log::error('Paraşüt fatura oluşturma hatası: ' . $e->getMessage(), [
            'invoice_data' => $invoiceData,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

public function findInvoiceByNumber(?string $invoiceNumber): ?array
{
    // Null veya boş gelirse direkt null dön
    if (empty($invoiceNumber)) {
        return null;
    }

    try {
        Log::info('Paraşüt\'te fatura aranıyor', ['invoice_number' => $invoiceNumber]);
        
        $response = $this->makeRequest('get', 'sales_invoices', [
            'filter[invoice_no]' => $invoiceNumber,
            'page[size]' => 1
        ]);

        if (!empty($response['data']) && count($response['data']) > 0) {
            Log::info('Fatura bulundu', [
                'parasut_id' => $response['data'][0]['id']
            ]);
            return $response['data'][0];
        }

        Log::warning('Fatura bulunamadı', ['invoice_number' => $invoiceNumber]);
        return null;

    } catch (Exception $e) {
        Log::error('Fatura arama hatası: ' . $e->getMessage());
        return null;
    }
}

/**
 * Fatura ID'sini getir veya ara
 */
public function getInvoiceId($invoice): ?string
{
    // Zaten kaydedilmiş ID varsa döndür
    if (!empty($invoice->integration_invoice_id)) {
        return $invoice->integration_invoice_id;
    }

    // EĞER FATURA NUMARASI YOKSA ARAMA YAPMA (HATA ÇÖZÜMÜ BURASI)
    if (empty($invoice->faturaNumarasi)) {
        Log::warning('Fatura numarası boş olduğu için Paraşüt\'te arama yapılamadı.', [
            'local_invoice_id' => $invoice->id
        ]);
        return null;
    }

    // Yoksa Paraşüt'te ara
    $parasutInvoice = $this->findInvoiceByNumber($invoice->faturaNumarasi);
    
    if ($parasutInvoice) {
        $parasutId = $parasutInvoice['id'];
        
        // Bulunan ID'yi veritabanına kaydet
        $invoice->integration_invoice_id = $parasutId;
        $invoice->save();
        
        Log::info('Paraşüt fatura ID\'si veritabanına kaydedildi', [
            'invoice_id' => $invoice->id,
            'parasut_id' => $parasutId
        ]);
        
        return $parasutId;
    }

    return null;
}

public function addPayment(string $invoiceId, array $paymentData): array
{
    try {
        Log::info('Faturaya ödeme ekleniyor', [
            'invoice_id' => $invoiceId,
            'amount' => $paymentData['amount']
        ]);

        $parasutPaymentData = [
            'data' => [
                'type' => 'payments',
                'attributes' => [
                    'description' => $paymentData['description'] ?? 'Tahsilat',
                    'account_id' => $paymentData['account_id'],
                    'date' => $paymentData['date'],
                    'amount' => (float) $paymentData['amount'],
                    'exchange_rate' => $paymentData['exchange_rate'] ?? null,
                ]
            ]
        ];

        $response = $this->makeRequest('post', "sales_invoices/{$invoiceId}/payments", $parasutPaymentData);

        Log::info('Ödeme başarıyla eklendi', [
            'payment_id' => $response['data']['id']
        ]);

        return [
            'success' => true,
            'payment_id' => $response['data']['id'],
            'data' => $response['data']
        ];

    } catch (Exception $e) {
        Log::error('Paraşüt ödeme ekleme hatası: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Kasa/Banka hesaplarını getir
 */
public function getAccounts(): array
{
    try {
        $response = $this->makeRequest('get', 'accounts', [
           
        ]);

        return [
            'success' => true,
            'accounts' => $response['data'] ?? []
        ];

    } catch (Exception $e) {
        Log::error('Hesaplar getirme hatası: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'accounts' => []
        ];
    }
}

/**
 * Faturanın ödemelerini getir
 */
public function getInvoicePayments(string $invoiceId): array
{
    try {
        $response = $this->makeRequest('get', "sales_invoices/{$invoiceId}", [
            'include' => 'payments'
        ]);

        $payments = [];
        if (!empty($response['included'])) {
            foreach ($response['included'] as $item) {
                if ($item['type'] === 'payments') {
                    $payments[] = $item;
                }
            }
        }

        return [
            'success' => true,
            'payments' => $payments,
            'remaining_amount' => $response['data']['attributes']['remaining'] ?? 0
        ];

    } catch (Exception $e) {
        Log::error('Fatura ödemeleri getirme hatası: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'payments' => []
        ];
    }
}

public function deleteInvoicePayment($invoiceId, $paymentId)
{
    try {
        Log::info('Tahsilat silme işlemi başlatıldı', [
            'invoice_id' => $invoiceId,
            'payment_id' => $paymentId,
            'payment_id_type' => gettype($paymentId)
        ]);

        // Önce tahsilat detaylarını transaction ile birlikte al
        $url = "{$this->baseUrl}/v4/{$this->companyId}/sales_invoices/{$invoiceId}?include=payments.transaction";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken, // ✅ DÜZELTİLDİ
            'Accept' => 'application/json',
        ])->get($url);

        usleep(200000); // Rate limit
        
        if (!$response->successful()) {
            Log::error('Paraşüt tahsilat bilgisi alınamadı', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return [
                'success' => false,
                'message' => 'Tahsilat bilgisi alınamadı'
            ];
        }

        $data = $response->json();
        
        // Payment'ı ve transaction'ı bul
        $transactionId = null;
        $foundPayment = null;
        
        if (isset($data['included'])) {
            foreach ($data['included'] as $item) {
                if ($item['type'] === 'payments') {
                    // ✅ Hem string hem integer karşılaştırması
                    if ($item['id'] == $paymentId || (string)$item['id'] === (string)$paymentId) {
                        $foundPayment = $item;
                        // Payment'ın transaction relationship'ini bul
                        if (isset($item['relationships']['transaction']['data']['id'])) {
                            $transactionId = $item['relationships']['transaction']['data']['id'];
                            break;
                        }
                    }
                }
            }
        }

        if (!$transactionId) {
            Log::error('Transaction ID bulunamadı', [
                'payment_id' => $paymentId,
                'invoice_id' => $invoiceId,
                'found_payment' => $foundPayment,
                'all_payments' => array_filter($data['included'] ?? [], function($item) {
                    return $item['type'] === 'payments';
                })
            ]);
            
            return [
                'success' => false,
                'message' => 'Tahsilat işlemi bulunamadı. Payment ID eşleşmedi.'
            ];
        }

        Log::info('Transaction bulundu', [
            'transaction_id' => $transactionId,
            'payment_id' => $paymentId
        ]);

        // Transaction'ı sil
        $deleteUrl = "{$this->baseUrl}/v4/{$this->companyId}/transactions/{$transactionId}";
        
        $deleteResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken, // ✅ DÜZELTİLDİ
            'Accept' => 'application/json',
        ])->delete($deleteUrl);

        usleep(200000); // Rate limit

        if ($deleteResponse->successful() || $deleteResponse->status() === 204) {
            Log::info('Paraşüt tahsilat silindi', [
                'payment_id' => $paymentId,
                'transaction_id' => $transactionId
            ]);
            
            return [
                'success' => true,
                'message' => 'Tahsilat başarıyla silindi',
                'transaction_id' => $transactionId
            ];
        }

        Log::error('Paraşüt tahsilat silinemedi', [
            'status' => $deleteResponse->status(),
            'body' => $deleteResponse->body()
        ]);

        return [
            'success' => false,
            'message' => 'Tahsilat silinemedi: ' . $deleteResponse->body()
        ];

    } catch (\Exception $e) {
        Log::error('Paraşüt tahsilat silme hatası: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'message' => 'Bir hata oluştu: ' . $e->getMessage()
        ];
    }
}

/**
 * Müşterinin e-Fatura mükellefiyetini kontrol et
 */
public function checkCustomerEInvoiceStatus(string $vkn): array
{
    try {
        Log::info('Müşteri e-fatura durumu kontrol ediliyor', ['vkn' => $vkn]);
        
        // VKN boşsa e-arşiv
        if (empty($vkn)) {
            Log::info('VKN boş, e-arşiv olarak işlem yapılacak');
            return [
                'success' => true,
                'is_e_invoice_user' => false,
                'type' => 'e-archive',
                'reason' => 'VKN bulunamadı'
            ];
        }
        
        // VKN ile e_invoice_inboxes'dan ara
        $response = $this->makeRequest('get', 'e_invoice_inboxes', [
            'filter[vkn]' => $vkn,
            'page[size]' => 1
        ]);
        
        // E-Fatura mükellefi mi?
        $isEInvoiceUser = !empty($response['data']) && count($response['data']) > 0;
        
        // ✅ e_invoice_address bilgisini al
        $eInvoiceAddress = null;
        if ($isEInvoiceUser) {
            $eInvoiceAddress = $response['data'][0]['attributes']['e_invoice_address'] ?? null;
        }
        
        Log::info('Müşteri e-fatura durumu kontrol edildi', [
            'vkn' => $vkn,
            'is_e_invoice_user' => $isEInvoiceUser,
            'e_invoice_address' => $eInvoiceAddress,
            'inbox_count' => count($response['data'] ?? [])
        ]);
        
        return [
            'success' => true,
            'is_e_invoice_user' => $isEInvoiceUser,
            'type' => $isEInvoiceUser ? 'e-invoice' : 'e-archive',
            'e_invoice_address' => $eInvoiceAddress, // ✅ DOĞRU ALAN
            'inbox_data' => $response['data'][0] ?? null
        ];
        
    } catch (Exception $e) {
        Log::error('E-fatura durumu kontrol hatası: ' . $e->getMessage(), [
            'vkn' => $vkn,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Hata durumunda güvenli taraf: e-arşiv
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'is_e_invoice_user' => false,
            'type' => 'e-archive'
        ];
    }
}

public function getEInvoiceAddress(string $vkn): ?string
{
    try {
        Log::info('E-Fatura adresi alınıyor', ['vkn' => $vkn]);
        
        $response = $this->makeRequest('get', 'e_invoice_inboxes', [
            'filter[vkn]' => $vkn,
            'page[size]' => 1
        ]);
        
        if (!empty($response['data']) && count($response['data']) > 0) {
            // ✅ DOĞRU ALAN: e_invoice_address
            $eInvoiceAddress = $response['data'][0]['attributes']['e_invoice_address'] ?? null;
            
            Log::info('E-Fatura adresi bulundu', [
                'vkn' => $vkn,
                'e_invoice_address' => $eInvoiceAddress
            ]);
            
            return $eInvoiceAddress;
        }
        
        Log::warning('E-Fatura inbox bulunamadı', ['vkn' => $vkn]);
        return null;
        
    } catch (Exception $e) {
        Log::error('E-Fatura adresi alma hatası: ' . $e->getMessage());
        return null;
    }
}

/**
 * Contact bilgilerinden VKN/TCKN al
 */
public function getContactTaxNumber(string $contactId): ?string
{
    try {
        $response = $this->makeRequest('get', "contacts/{$contactId}");
        
        // tax_number attribute'unu al
        $taxNumber = $response['data']['attributes']['tax_number'] ?? null;
        
        Log::info('Contact tax number alındı', [
            'contact_id' => $contactId,
            'tax_number' => $taxNumber ? '***' . substr($taxNumber, -4) : 'yok' // Güvenlik için maskelenmiş
        ]);
        
        return $taxNumber;
        
    } catch (Exception $e) {
        Log::error('Contact tax number alma hatası: ' . $e->getMessage());
        return null;
    }
}

/**
 * e-Fatura olarak resmileştir
 */
public function formalizeAsEInvoice(string $invoiceId, string $vkn, string $scenario = 'basic'): array
{
    try {
        Log::info('Fatura e-Fatura olarak resmileştiriliyor', [
            'invoice_id' => $invoiceId,
            'vkn' => $vkn,
            'scenario' => $scenario
        ]);
        
        // ✅ VKN ile e-Fatura adresini al
        $eInvoiceAddress = $this->getEInvoiceAddress($vkn);
        
        if (!$eInvoiceAddress) {
            throw new Exception("VKN '{$vkn}' için e-Fatura adresi bulunamadı. Müşteri e-Fatura mükellefi olmayabilir.");
        }
        
        // ✅ Attributes'u doğru şekilde hazırla
        $attributes = [
            'scenario' => $scenario, // 'basic' veya 'commercial'
            'to' => $eInvoiceAddress, // ✅ ZORUNLU: Alıcının e-Fatura adresi (örn: "urn:mail:defaultpk@parasut.com")
        ];

        $data = [
            'data' => [
                'id' => $invoiceId,
                'type' => 'e_invoices',
                'attributes' => $attributes,
                'relationships' => [
                    'invoice' => [
                        'data' => [
                            'id' => $invoiceId,
                            'type' => 'sales_invoices'
                        ]
                    ]
                ]
            ]
        ];

        Log::info('e-Fatura API isteği gönderiliyor', [
            'invoice_id' => $invoiceId,
            'to' => $eInvoiceAddress,
            'scenario' => $scenario
        ]);

        $response = $this->makeRequest('post', 'e_invoices', $data);
        
        $jobId = $response['data']['id'] ?? null;
        
        Log::info('e-Fatura resmileştirme kuyruğa alındı', [
            'job_id' => $jobId,
            'invoice_id' => $invoiceId
        ]);
        
        return [
            'success' => true,
            'type' => 'e-invoice',
            'job_id' => $jobId,
            'e_invoice_address' => $eInvoiceAddress,
            'message' => 'Fatura e-Fatura olarak resmileştirme kuyruğuna alındı.'
        ];
        
    } catch (Exception $e) {
        Log::error('e-Fatura resmileştirme hatası: ' . $e->getMessage(), [
            'invoice_id' => $invoiceId,
            'vkn' => $vkn,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * e-Arşiv olarak resmileştir
 */
public function formalizeAsEArchive(string $invoiceId, bool $isInternetSale = false): array
    {
        try {
            Log::info('Fatura e-Arşiv olarak resmileştiriliyor', ['invoice_id' => $invoiceId]);
            
            $attributes = [];
            
            // Eğer internet satışı ise bu bilgileri ekle, değilse boş gönder
            if ($isInternetSale) {
                $attributes['internet_sale'] = [
                    'url' => config('app.url'),
                    'payment_type' => 'ODEMEARACISI', // Veya KREDIKARTI/HAVALE
                    'payment_platform' => 'ServisCRM',
                    'payment_date' => now()->toDateString()
                ];
            }

            $data = [
                'data' => [
                    'id' => $invoiceId,
                    'type' => 'e_archives',
                    'attributes' => $attributes, // Boş array giderse normal e-Arşiv olur
                    'relationships' => [
                        'sales_invoice' => [
                            'data' => [
                                'id' => $invoiceId,
                                'type' => 'sales_invoices'
                            ]
                        ]
                    ]
                ]
            ];

            $response = $this->makeRequest('post', "e_archives", $data);
            
            $jobId = $response['data']['id'] ?? null;
            
            return [
                'success' => true,
                'type' => 'e-archive',
                'job_id' => $jobId,
                'message' => 'Fatura e-Arşiv olarak resmileştirme kuyruğuna alındı.'
            ];
            
        } catch (Exception $e) {
            Log::error('e-Arşiv resmileştirme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
public function checkJobStatus(string $jobId): array
{
    try {
        $response = $this->makeRequest('get', "trackable_jobs/{$jobId}");
        
        $status = $response['data']['attributes']['status'] ?? 'unknown';
        $errors = $response['data']['attributes']['errors'] ?? [];
        
        // Paraşüt job status değerleri: pending, running, done, error
        $statusMap = [
            'pending' => 'pending',
            'running' => 'pending',
            'done' => 'sent',
            'error' => 'error'
        ];
        
        return [
            'success' => true,
            'status' => $statusMap[$status] ?? 'pending',
            'raw_status' => $status,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        Log::error('Job durumu kontrol hatası: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Resmileştirme durumunu kontrol et
 */
public function checkFormalizationStatus(string $invoiceId, bool $getPdfUrl = true): array
{
    try {
        Log::info('=== checkFormalizationStatus BAŞLADI ===', [
            'invoice_id' => $invoiceId,
            'getPdfUrl' => $getPdfUrl
        ]);
        
        // Faturayı getir
        $response = $this->makeRequest('get', "sales_invoices/{$invoiceId}", [
            'include' => 'active_e_document'
        ]);
        
        // ⭐ DEBUG: Raw response'u logla
        Log::info('Paraşüt sales_invoice response', [
            'invoice_id' => $invoiceId,
            'has_relationships' => isset($response['data']['relationships']),
            'relationships_keys' => isset($response['data']['relationships']) ? array_keys($response['data']['relationships']) : [],
            'active_e_document_exists' => isset($response['data']['relationships']['active_e_document']),
            'active_e_document_data' => $response['data']['relationships']['active_e_document'] ?? 'YOK'
        ]);
        
        // Paraşüt'teki güncel fatura numarasını al
        $officialInvoiceNumber = $response['data']['attributes']['invoice_no'] ?? null;

        // e-document kontrolü
        if (!isset($response['data']['relationships']['active_e_document']['data'])) {
            Log::warning('active_e_document YOK veya data null', [
                'invoice_id' => $invoiceId
            ]);
            
            return [
                'success' => true, 
                'formalized' => false, 
                'status' => null, 
                'type' => null,
                'invoice_number' => $officialInvoiceNumber
            ];
        }
        
        $eDocumentData = $response['data']['relationships']['active_e_document']['data'];
        $eDocumentId = $eDocumentData['id'];
        $eDocumentType = $eDocumentData['type'];

        Log::info('e-Document bulundu', [
            'e_document_id' => $eDocumentId,
            'e_document_type' => $eDocumentType
        ]);

        // Detayları al
        $eDocResponse = $this->makeRequest('get', "{$eDocumentType}/{$eDocumentId}");
        
        $eDocStatus = $eDocResponse['data']['attributes']['status'] ?? 'unknown';
        $attributesPdfUrl = $eDocResponse['data']['attributes']['pdf_url'] ?? null;

        // ⭐ DEBUG: e-Document detaylarını logla
        Log::info('e-Document detayları', [
            'e_document_id' => $eDocumentId,
            'e_document_type' => $eDocumentType,
            'raw_status' => $eDocStatus,
            'all_attributes' => $eDocResponse['data']['attributes'] ?? []
        ]);
        
        // Status haritası
        $statusMap = [
            'waiting_for_approval' => 'pending',
            'queued' => 'pending',
            'running' => 'pending',
            'approved' => 'sent',
            'sent' => 'sent',
            'printed' => 'sent',
            'signed' => 'sent',
            'legalized' => 'sent',
            'successful' => 'sent',
            'rejected' => 'error',
            'failed' => 'error',
            'unknown' => 'pending'
        ];
        
        $localStatus = $statusMap[$eDocStatus] ?? 'pending';
        
        Log::info('Status mapping', [
            'raw_status' => $eDocStatus,
            'mapped_status' => $localStatus,
            'is_in_map' => isset($statusMap[$eDocStatus])
        ]);

        $pdfUrl = null;
        $pdfExpiresAt = null;
        
        // ⭐ PDF alma koşulunu logla
        Log::info('PDF alma koşulu kontrolü', [
            'localStatus' => $localStatus,
            'getPdfUrl' => $getPdfUrl,
            'condition_met' => ($localStatus === 'sent' && $getPdfUrl)
        ]);
        
        if ($localStatus === 'sent' && $getPdfUrl) {
    // ✅ Önce attributes'taki PDF URL'i kontrol et
    if ($attributesPdfUrl) {
        // Relative URL ise full URL'e çevir
        if (strpos($attributesPdfUrl, 'http') !== 0) {
            $pdfUrl = "https://uygulama.heroku-staging.parasut.com" . $attributesPdfUrl;
        } else {
            $pdfUrl = $attributesPdfUrl;
        }
        $pdfExpiresAt = null; // Attributes'tan gelen URL'de expire yok
        
        Log::info('PDF URL attributes\'tan alındı', [
            'pdf_url' => $pdfUrl
        ]);
    } else {
        // Attributes'ta yoksa API'den al
        Log::info('PDF URL API\'den alınıyor...');
        $pdfResult = $this->waitAndGetPdfUrl($eDocumentType, $eDocumentId, 10, 3);
        
        if ($pdfResult['success']) {
            $pdfUrl = $pdfResult['url'];
            $pdfExpiresAt = $pdfResult['expires_at'];
        }
    }
}

        return [
            'success' => true,
            'formalized' => true,
            'status' => $localStatus,
            'raw_status' => $eDocStatus,
            'type' => $eDocumentType === 'e_invoices' ? 'e-invoice' : 'e-archive',
            'pdf_url' => $pdfUrl,
            'pdf_expires_at' => $pdfExpiresAt,
            'invoice_number' => $officialInvoiceNumber,
            'e_document_id' => $eDocumentId
        ];
        
    } catch (Exception $e) {
        Log::error('Resmileştirme durumu kontrol hatası: ' . $e->getMessage(), [
            'invoice_id' => $invoiceId,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false, 
            'error' => $e->getMessage()
        ];
    }
}
/**
 * e-Arşiv PDF URL'ini al (hazırsa)
 */
public function getEArchivePdfUrl(string $eArchiveId): array
{
    try {
        Log::info('e-Arşiv PDF URL alınıyor', ['e_archive_id' => $eArchiveId]);
        
        // ✅ DÜZELTİLDİ: /v4/{company_id}/e_archives/{id}/pdf
        $response = Http::withToken($this->accessToken)
            ->accept('application/json')
            ->get("{$this->baseUrl}/v4/{$this->companyId}/e_archives/{$eArchiveId}/pdf");
        
        // PDF henüz hazır değil
        if ($response->status() === 204) {
            Log::info('e-Arşiv PDF henüz hazır değil', ['e_archive_id' => $eArchiveId]);
            return [
                'success' => false,
                'ready' => false,
                'message' => 'PDF henüz hazır değil'
            ];
        }
        
        if ($response->successful()) {
            $data = $response->json();
            $pdfUrl = $data['data']['attributes']['url'] ?? null;
            $expiresAt = $data['data']['attributes']['expires_at'] ?? null;
            
            Log::info('e-Arşiv PDF URL alındı', [
                'e_archive_id' => $eArchiveId,
                'expires_at' => $expiresAt
            ]);
            
            return [
                'success' => true,
                'ready' => true,
                'url' => $pdfUrl,
                'expires_at' => $expiresAt
            ];
        }
        
        throw new Exception('PDF URL alınamadı: ' . $response->body());
        
    } catch (Exception $e) {
        Log::error('e-Arşiv PDF URL alma hatası: ' . $e->getMessage());
        return [
            'success' => false,
            'ready' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * e-Fatura PDF URL'ini al (hazırsa)
 */
public function getEInvoicePdfUrl(string $eInvoiceId): array
{
    try {
        Log::info('e-Fatura PDF URL alınıyor', ['e_invoice_id' => $eInvoiceId]);
        
        // ✅ DÜZELTİLDİ: /v4/{company_id}/e_invoices/{id}/pdf
        $response = Http::withToken($this->accessToken)
            ->accept('application/json')
            ->get("{$this->baseUrl}/v4/{$this->companyId}/e_invoices/{$eInvoiceId}/pdf");
        
        // PDF henüz hazır değil
        if ($response->status() === 204) {
            Log::info('e-Fatura PDF henüz hazır değil', ['e_invoice_id' => $eInvoiceId]);
            return [
                'success' => false,
                'ready' => false,
                'message' => 'PDF henüz hazır değil'
            ];
        }
        
        if ($response->successful()) {
            $data = $response->json();
            $pdfUrl = $data['data']['attributes']['url'] ?? null;
            $expiresAt = $data['data']['attributes']['expires_at'] ?? null;
            
            Log::info('e-Fatura PDF URL alındı', [
                'e_invoice_id' => $eInvoiceId,
                'expires_at' => $expiresAt
            ]);
            
            return [
                'success' => true,
                'ready' => true,
                'url' => $pdfUrl,
                'expires_at' => $expiresAt
            ];
        }
        
        throw new Exception('PDF URL alınamadı: ' . $response->body());
        
    } catch (Exception $e) {
        Log::error('e-Fatura PDF URL alma hatası: ' . $e->getMessage());
        return [
            'success' => false,
            'ready' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * PDF URL'ini bekleyerek al (retry ile)
 * @param string $documentType 'e_archive' veya 'e_invoice'
 * @param string $documentId e-Arşiv veya e-Fatura ID'si
 * @param int $maxRetries Maksimum deneme sayısı
 * @param int $retryDelay Denemeler arası bekleme süresi (saniye)
 */
public function waitAndGetPdfUrl(string $documentType, string $documentId, int $maxRetries = 10, int $retryDelay = 3): array
{
    Log::info('PDF URL bekleniyor', [
        'type' => $documentType,
        'document_id' => $documentId,
        'max_retries' => $maxRetries,
        'retry_delay' => $retryDelay
    ]);
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        Log::info("PDF URL kontrol ediliyor (Deneme {$attempt}/{$maxRetries})", [
            'type' => $documentType,
            'document_id' => $documentId
        ]);
        
        // PDF URL'ini kontrol et
        if ($documentType === 'e_document_pdfs') {
            $pdfResult = $this->getEArchivePdfUrl($documentId);
        } else {
            $pdfResult = $this->getEInvoicePdfUrl($documentId);
        }
        
        if ($pdfResult['success'] && $pdfResult['ready']) {
            Log::info('PDF URL hazır', [
                'type' => $documentType,
                'document_id' => $documentId,
                'attempts' => $attempt
            ]);
            
            return [
                'success' => true,
                'url' => $pdfResult['url'],
                'expires_at' => $pdfResult['expires_at'],
                'attempts' => $attempt
            ];
        }
        
        // Son denemede bile hazır değilse
        if ($attempt === $maxRetries) {
            Log::warning('PDF URL maksimum deneme sonrası hazır değil', [
                'type' => $documentType,
                'document_id' => $documentId,
                'attempts' => $attempt
            ]);
            
            return [
                'success' => false,
                'error' => 'PDF maksimum deneme sayısı sonrası hazır olmadı',
                'attempts' => $attempt
            ];
        }
        
        // Bekle ve tekrar dene
        sleep($retryDelay);
    }
    
    return [
        'success' => false,
        'error' => 'PDF URL alınamadı',
        'attempts' => $maxRetries
    ];
}
public function downloadEArchivePdf(string $eArchiveId): array
{
    try {
        $response = $this->makeRequest('get', "e_archives/{$eArchiveId}");
        
        $pdfUrl = $response['data']['attributes']['pdf_url'] ?? null;
        
        if (!$pdfUrl) {
            throw new Exception('PDF URL bulunamadı');
        }

        // PDF'i indir
        $pdfContent = Http::withToken($this->accessToken)->get($pdfUrl);
        
        if (!$pdfContent->successful()) {
            throw new Exception('PDF indirilemedi');
        }
        
        // Dosyayı kaydet
        $fileName = 'e_archive_' . $eArchiveId . '_' . time() . '.pdf';
        $path = 'invoices/e_archives/' . $fileName;
        
        Storage::disk('public')->put($path, $pdfContent->body());

        Log::info('e-Arşiv PDF indirildi', ['path' => $path]);

        return [
            'success' => true,
            'path' => 'storage/' . $path,
            'url' => asset('storage/' . $path)
        ];

    } catch (Exception $e) {
        Log::error('e-Arşiv PDF indirme hatası: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Paraşüt'teki faturayı güncelle (resmileştirilmemiş faturalar için)
 */
public function updateInvoice(string $parasutInvoiceId, array $invoiceData): array
{
    try {
        Log::info('Paraşüt faturası güncelleniyor', [
            'parasut_invoice_id' => $parasutInvoiceId,
            'invoice_number' => $invoiceData['faturaNumarasi'] ?? null
        ]);

        // 1. Önce mevcut faturayı al (ürün ID'leri için)
        $existingInvoice = $this->makeRequest('get', "sales_invoices/{$parasutInvoiceId}", [
            'include' => 'details,contact'
        ]);

        // 2. Müşteri ID'sini al
        $contactId = $existingInvoice['data']['relationships']['contact']['data']['id'] ?? null;
        
        if (!$contactId) {
            throw new Exception('Mevcut faturada müşteri bulunamadı');
        }

        // 3. Ürünleri senkronize et
        $productIds = $this->syncProducts($invoiceData['items']);

        // 4. Fatura detaylarını hazırla
        $details = [];
        foreach ($invoiceData['items'] as $index => $item) {
            $detail = [
                'type' => 'sales_invoice_details',
                'attributes' => [
                    'quantity' => (float) $item['miktar'],
                    'unit_price' => (float) $item['fiyat'],
                    'vat_rate' => (float) ($invoiceData['kdvTutar'] ?? 20),
                    'description' => $item['aciklama'] ?? '',
                ]
            ];

            // Ürün ID'sini ekle
            if (!empty($productIds[$index])) {
                $detail['relationships'] = [
                    'product' => [
                        'data' => [
                            'id' => $productIds[$index],
                            'type' => 'products'
                        ]
                    ]
                ];
            }

            $details[] = $detail;
        }

        // 5. Tevkifat oranını hesapla
        $withholding_rate = 0;
        if (!empty($invoiceData['vat_withholding_rate']) && $invoiceData['vat_withholding_rate'] > 0) {
            $withholding_rate = ($invoiceData['vat_withholding_rate'] * 10);
        }

        // 6. Güncelleme verisini hazırla
        $updateData = [
            'data' => [
                'id' => $parasutInvoiceId,
                'type' => 'sales_invoices',
                'attributes' => [
                    'item_type' => 'invoice',
                    'description' => $invoiceData['faturaAciklama'] ?? ($invoiceData['faturaNumarasi'] ?? 'Servis Faturası'),
                    'issue_date' => $invoiceData['faturaTarihi'],
                    'due_date' => $invoiceData['faturaTarihi'],
                    'currency' => 'TRL',
                    'withholding_rate' => 0,
                    'vat_withholding_rate' => $withholding_rate,
                    'invoice_discount_type' => 'amount',
                    'invoice_discount' => (float) ($invoiceData['indirim'] ?? 0),
                ],
                'relationships' => [
                    'contact' => [
                        'data' => [
                            'type' => 'contacts',
                            'id' => $contactId
                        ]
                    ],
                    'details' => [
                        'data' => $details
                    ]
                ]
            ]
        ];

        Log::info('Paraşüt API\'ye güncelleme gönderiliyor', [
            'parasut_invoice_id' => $parasutInvoiceId,
            'items_count' => count($details)
        ]);

        // 7. PUT isteği gönder
        $response = $this->makeRequest('put', "sales_invoices/{$parasutInvoiceId}", $updateData);

        Log::info('Fatura Paraşüt\'te güncellendi', [
            'parasut_invoice_id' => $parasutInvoiceId,
            'invoice_no' => $response['data']['attributes']['invoice_no'] ?? null
        ]);

        return [
            'success' => true,
            'invoice_id' => $parasutInvoiceId,
            'invoice_number' => $response['data']['attributes']['invoice_no'] ?? null,
            'data' => $response['data']
        ];

    } catch (Exception $e) {
        Log::error('Paraşüt fatura güncelleme hatası: ' . $e->getMessage(), [
            'parasut_invoice_id' => $parasutInvoiceId,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Faturanın Paraşüt'te güncellenip güncellenemeyeceğini kontrol et
 */
public function canUpdateInvoice(string $parasutInvoiceId): array
{
    try {
        $response = $this->makeRequest('get', "sales_invoices/{$parasutInvoiceId}", [
            'include' => 'active_e_document'
        ]);
        
        // e-document varsa güncelleme yapılamaz
        $hasEDocument = isset($response['data']['relationships']['active_e_document']['data']);
        
        return [
            'success' => true,
            'can_update' => !$hasEDocument,
            'reason' => $hasEDocument ? 'Fatura resmileştirilmiş, güncellenemez' : null
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'can_update' => false,
            'reason' => $e->getMessage()
        ];
    }
}
    protected function markAsEArchive(string $invoiceId)
    {
        try {
            Log::info('Fatura e-Arşiv olarak işaretleniyor', ['invoice_id' => $invoiceId]);
            
            $this->makeRequest('post', "sales_invoices/{$invoiceId}/e_document", [
                'data' => [
                    'type' => 'e_archives',
                    'attributes' => [
                        'internet_sale' => [
                            'url' => config('app.url'),
                            'payment_type' => 'ODEMEARACISI',
                            'payment_platform' => 'ServisCRM',
                            'payment_date' => now()->toDateString()
                        ]
                    ]
                ]
            ]);
            
            Log::info('Fatura e-Arşiv olarak işaretlendi');
            
        } catch (Exception $e) {
            Log::warning('e-Arşiv işaretleme hatası: ' . $e->getMessage());
        }
    }

    public function updateInvoiceStatus(string $invoiceId, string $status): array
    {
        return [
            'success' => true,
            'message' => 'Durum güncellendi'
        ];
    }

    public function downloadInvoicePdf(string $invoiceId): string
    {
        try {
            Log::info('PDF indiriliyor', ['invoice_id' => $invoiceId]);
            
            // PDF URL'ini al
            $response = $this->makeRequest('get', "sales_invoices/{$invoiceId}");
            
            $pdfUrl = $response['data']['attributes']['pdf']['url'] ?? null;
            
            if (!$pdfUrl) {
                throw new Exception('PDF URL bulunamadı');
            }

            Log::info('PDF URL bulundu', ['url' => $pdfUrl]);

            // PDF'i indir
            $pdfContent = Http::withToken($this->accessToken)->get($pdfUrl)->body();
            
            // Dosyayı kaydet
            $fileName = 'invoice_' . $invoiceId . '_' . time() . '.pdf';
            $path = 'invoices/' . $fileName;
            
            Storage::disk('public')->put($path, $pdfContent);

            Log::info('PDF indirildi', ['path' => $path]);

            return 'storage/' . $path;

        } catch (Exception $e) {
            Log::error('PDF indirme hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    public function testConnection(): bool
    {
        try {
            Log::info('Paraşüt bağlantısı test ediliyor');
            
            $this->makeRequest('get', 'contacts', [
                'page[size]' => 1
            ]);
            
            Log::info('Paraşüt bağlantısı başarılı');
            return true;
        } catch (Exception $e) {
            Log::error('Paraşüt bağlantı testi başarısız: ' . $e->getMessage());
            return false;
        }
    }
}