<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class ParasutService
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $username;
    private $password;
    private $companyId;
    private $redirectUri;

    public function __construct()
    {
        $this->baseUrl = config('parasut.api_url');
        $this->clientId = config('parasut.client_id');
        $this->clientSecret = config('parasut.client_secret');
        $this->username = config('parasut.username');
        $this->password = config('parasut.password');
        $this->companyId = config('parasut.company_id');
        $this->redirectUri = config('parasut.redirect_uri');
    }

    /**
     * Access Token Al (Cache ile)
     */
    public function getAccessToken()
{
    // Cache'i geçici olarak devre dışı bırakalım test için
    // return Cache::remember('parasut_access_token', 7000, function () {
        try {
            $requestData = [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
                'redirect_uri' => $this->redirectUri
            ];

            Log::info('Paraşüt Token İsteği Gönderiliyor', [
                'url' => $this->baseUrl . '/oauth/token',
                'client_id' => $this->clientId,
                'username' => $this->username,
                'has_password' => !empty($this->password),
                'has_secret' => !empty($this->clientSecret),
            ]);

            $response = Http::asForm()->post($this->baseUrl . '/oauth/token', $requestData);

            Log::info('Paraşüt Token Yanıtı', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Paraşüt Token Alındı', ['expires_in' => $data['expires_in']]);
                return $data['access_token'];
            }

            Log::error('Paraşüt Token Hatası', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new Exception('Token alınamadı: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Paraşüt Token Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    // });
}
    /**
     * API İsteği Yap
     */
    private function request($method, $endpoint, $data = [])
    {
        try {
            $token = $this->getAccessToken();
            $url = "{$this->baseUrl}/v4/{$this->companyId}/{$endpoint}";

            Log::info('Paraşüt API İsteği', [
                'method' => $method,
                'url' => $url,
                'data' => $data
            ]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->$method($url, $data);

            if ($response->successful()) {
                Log::info('Paraşüt API Başarılı', ['response' => $response->json()]);
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status()
                ];
            }

            // Token süresi dolmuşsa tekrar dene
            if ($response->status() == 401) {
                Log::warning('Token süresi dolmuş, yenileniyor...');
                Cache::forget('parasut_access_token');
                $token = $this->getAccessToken();
                
                $response = Http::withToken($token)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])
                    ->$method($url, $data);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'status' => $response->status()
                    ];
                }
            }

            Log::error('Paraşüt API Hatası', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Paraşüt API Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test: Bağlantı Kontrolü
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'Bağlantı başarılı!',
                'token' => substr($token, 0, 20) . '...'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Müşteri Oluştur
     */
    public function createContact($contactData)
    {
        $data = [
            'data' => [
                'type' => 'contacts',
                'attributes' => $contactData
            ]
        ];

        return $this->request('post', 'contacts', $data);
    }


    /**
     * Satış Faturası Oluştur 
     */
    public function createInvoice($contactId, $items, $invoiceData = [])
    {
        // Fatura kalemleri hazırla
        $details = [];
        
        foreach ($items as $item) {
            $detail = [
                'type' => 'sales_invoice_details',
                'attributes' => [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'vat_rate' => $item['vat_rate'] ?? 20,
                    'description' => $item['description'] ?? ''
                ],
                'relationships' => [
                    'product' => [
                        'data' => [
                            'id' => (string)$item['product_id'],
                            'type' => 'products'
                        ]
                    ]
                ]
            ];
            
            $details[] = $detail;
        }

        $data = [
            'data' => [
                'type' => 'sales_invoices',
                'attributes' => [
                    'item_type' => $invoiceData['item_type'] ?? 'invoice',
                    'description' => $invoiceData['description'] ?? '',
                    'issue_date' => $invoiceData['issue_date'] ?? date('Y-m-d'),
                    'due_date' => $invoiceData['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                    'currency' => $invoiceData['currency'] ?? 'TRL',
                    'exchange_rate' => $invoiceData['exchange_rate'] ?? 1
                ],
                'relationships' => [
                    'details' => [
                        'data' => $details  // Array olarak
                    ],
                    'contact' => [
                        'data' => [
                            'id' => (string)$contactId,
                            'type' => 'contacts'
                        ]
                    ]
                ]
            ]
        ];

        Log::info('Fatura Oluşturma İsteği (YENİ)', ['data' => json_encode($data, JSON_PRETTY_PRINT)]);

        return $this->request('post', 'sales_invoices', $data);
    }
    public function createProduct($productData)
        {
            $data = [
                'data' => [
                    'type' => 'products',
                    'attributes' => $productData
                ]
            ];

            return $this->request('post', 'products', $data);
    }

    /**
     * E-Arşiv Fatura Oluştur
     */
    public function createEArchive($invoiceId, $internetSale = null)
    {
        $data = [
            'data' => [
                'type' => 'e_archives',
                'relationships' => [
                    'sales_invoice' => [
                        'data' => [
                            'id' => (string)$invoiceId,
                            'type' => 'sales_invoices'
                        ]
                    ]
                ]
            ]
        ];

        // İnternet satışı bilgileri varsa ekle
        if ($internetSale) {
            $data['data']['attributes'] = [
                'internet_sale' => [
                    'url' => $internetSale['url'] ?? config('app.url'),
                    'payment_type' => $internetSale['payment_type'] ?? 'ODEMEARACISI',
                    'payment_platform' => $internetSale['payment_platform'] ?? 'Ödeme Sistemi',
                    'payment_date' => $internetSale['payment_date'] ?? date('Y-m-d')
                ]
            ];
        }

        Log::info('E-Arşiv Oluşturma İsteği', ['data' => json_encode($data, JSON_PRETTY_PRINT)]);

        return $this->request('post', 'e_archives', $data);
    }

    /**
     * E-Fatura Oluştur
     */
    public function createEInvoice($invoiceId, $eInvoiceAddress, $scenario = 'basic')
    {
        $data = [
            'data' => [
                'type' => 'e_invoices',
                'attributes' => [
                    'scenario' => $scenario, // basic veya commercial
                    'to' => $eInvoiceAddress
                ],
                'relationships' => [
                    'invoice' => [
                        'data' => [
                            'id' => (string)$invoiceId,
                            'type' => 'sales_invoices'
                        ]
                    ]
                ]
            ]
        ];

        Log::info('E-Fatura Oluşturma İsteği', ['data' => json_encode($data, JSON_PRETTY_PRINT)]);

        return $this->request('post', 'e_invoices', $data);
    }

    /**
     * VKN E-Fatura Kontrolü
     */
    public function checkVknType($taxNumber)
    {
        $url = "{$this->baseUrl}/v4/{$this->companyId}/e_invoice_inboxes/vkn_check?vkn={$taxNumber}";
        
        try {
            $token = $this->getAccessToken();
            
            $response = Http::withToken($token)
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'is_e_invoice' => !empty($response->json()['result'])
                ];
            }

            return [
                'success' => false,
                'is_e_invoice' => false
            ];

        } catch (Exception $e) {
            Log::error('VKN Kontrol Hatası', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'is_e_invoice' => false
            ];
        }
    }

    /**
     * Trackable Job Durumunu Kontrol Et
     */
    public function checkJobStatus($jobId)
    {
        return $this->request('get', "trackable_jobs/{$jobId}");
    }

    /**
     * E-Arşiv PDF İndir
     */
    public function getEArchivePdf($eArchiveId)
    {
        return $this->request('get', "e_archives/{$eArchiveId}/pdf");
    }

    /**
     * E-Fatura PDF İndir
     */
    public function getEInvoicePdf($eInvoiceId)
    {
        return $this->request('get', "e_invoices/{$eInvoiceId}/pdf");
    }

    
    
    }