<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\DataTables;


class PaymentHistoryController extends Controller
{
    public function index(Request $request, $tenant_id)
{
    abort_if(!auth()->user()->hasRole('Patron'), 403);

    $tenant = Tenant::where('id', $tenant_id)->first();
    
    if ($request->ajax()) {
        // Filtreleme parametreleri
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $paymentMethod = $request->get('payment_method');
        $status = $request->get('status');
        $type = $request->get('type', 'all');
        $search = $request->get('search')['value'] ?? '';

        $allPayments = collect();

        // Abonelik ödemeleri
        if (in_array($type, ['all', 'subscription'])) {
            if (method_exists($tenant, 'subscriptionPayments')) {
                $subscriptionPayments = $tenant->subscriptionPayments()
                    ->where('status', 'completed')
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->when($paymentMethod, function($query) use ($paymentMethod) {
                        return $query->where('payment_method', $paymentMethod);
                    })
                    ->when($status, function($query) use ($status) {
                        return $query->where('status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'type' => 'subscription',
                            'type_label' => 'Abonelik',
                            'description' => $this->getSubscriptionPaymentDescription($payment),
                            'amount' => number_format($payment->amount ?? 0, 2) . ' ' . strtoupper($payment->currency ?? 'TL'),
                            'status' => $payment->status,
                            'status_label' => $this->getStatusLabel($payment->status),
                            'invoice_status' => !empty($payment->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                            'invoice_path' => $payment->invoice_path,
                            'created_at' => $payment->created_at->format('d.m.Y'),
                            'created_at_timestamp' => $payment->created_at->timestamp
                        ];
                    });

                $allPayments = $allPayments->concat($subscriptionPayments);
            }
        }

        // Depolama ödemeleri
        if (in_array($type, ['all', 'storage'])) {
            if (method_exists($tenant, 'storagePurchases')) {
                $storagePurchases = $tenant->storagePurchases()
                    ->where('status', 'completed')
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->when($paymentMethod, function($query) use ($paymentMethod) {
                        return $query->where(function($q) use ($paymentMethod) {
                            $q->whereJsonContains('payment_response->payment_type', 'card')
                              ->orWhere('payment_method', $paymentMethod);
                        });
                    })
                    ->when($status, function($query) use ($status) {
                        return $query->where('status', $status)
                                     ->orWhereJsonContains('payment_response->status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($purchase) {
                        $paymentResponse = is_string($purchase->payment_response) 
                            ? json_decode($purchase->payment_response, true) 
                            : $purchase->payment_response;
                            
                        return [
                            'id' => $purchase->id,
                            'type' => 'storage',
                            'type_label' => 'Depolama',
                            'description' => $this->getStorageDescription($purchase),
                            'amount' => number_format($purchase->amount, 2) . ' ' . strtoupper($paymentResponse['currency'] ?? 'TL'),
                            'status' => $purchase->status,
                            'status_label' => $this->getStatusLabel($purchase->status),
                            'invoice_status' => !empty($purchase->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                            'invoice_path' => $purchase->invoice_path,
                            'created_at' => $purchase->created_at->format('d.m.Y'),
                            'created_at_timestamp' => $purchase->created_at->timestamp
                        ];
                    });

                $allPayments = $allPayments->concat($storagePurchases);
            }
        }

        // Entegrasyon ödemeleri
        if (in_array($type, ['all', 'integration'])) {
            if (method_exists($tenant, 'integrationPurchases')) {
                $integrationPurchases = $tenant->integrationPurchases()
                    ->with('integration')
                    ->where('status', 'completed')
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->when($paymentMethod, function($query) use ($paymentMethod) {
                        return $query->where(function($q) use ($paymentMethod) {
                            $q->whereJsonContains('payment_response->payment_type', 'card')
                              ->orWhere('payment_method', $paymentMethod);
                        });
                    })
                    ->when($status, function($query) use ($status) {
                        return $query->where('status', $status)
                                     ->orWhereJsonContains('payment_response->status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($purchase) {
                        $paymentResponse = is_string($purchase->payment_response) 
                            ? json_decode($purchase->payment_response, true) 
                            : $purchase->payment_response;
                            
                        return [
                            'id' => $purchase->id,
                            'type' => 'integration',
                            'type_label' => 'Entegrasyon',
                            'description' => $this->getIntegrationDescription($purchase),
                            'amount' => number_format($purchase->amount, 2) . ' ' . strtoupper($paymentResponse['currency'] ?? 'TL'),
                            'status' => $purchase->status,
                            'status_label' => $this->getStatusLabel($purchase->status),
                            'invoice_status' => !empty($purchase->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                            'invoice_path' => $purchase->invoice_path,
                            'created_at' => $purchase->created_at->format('d.m.Y'),
                            'created_at_timestamp' => $purchase->created_at->timestamp
                        ];
                    });

                $allPayments = $allPayments->concat($integrationPurchases);
            }
        }

        // Arama filtresi
        if (!empty($search)) {
            $allPayments = $allPayments->filter(function($payment) use ($search) {
                return stripos($payment['description'], $search) !== false ||
                       stripos($payment['type_label'], $search) !== false;
            });
        }

        // Sıralama
        $allPayments = $allPayments->sortByDesc('created_at_timestamp')->values();

        return DataTables::of($allPayments)
            ->addIndexColumn()
            ->editColumn('id', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['id'] . '</a>';
            })
            ->editColumn('type_label', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['type_label'] . '</a>';
            })
            ->editColumn('description', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['description'] . '</a>';
            })
            ->editColumn('amount', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['amount'] . '</a>';
            })
            ->editColumn('status_label', function($row) {
                $statusColor = match($row['status']) {
                    'active', 'completed' => '#28a745',
                    'pending' => '#fd7e14',
                    'cancelled', 'failed' => '#dc3545',
                    'expired' => '#6c757d',
                    default => '#343a40'
                };
                
                $icon = match($row['status']) {
                    'active', 'completed' => '<i class="fas fa-check-circle me-1"></i>',
                    'pending' => '<i class="fas fa-clock me-1"></i>',
                    'cancelled', 'failed' => '<i class="fas fa-times-circle me-1"></i>',
                    'expired' => '<i class="fas fa-ban me-1"></i>',
                    default => ''
                };
                
                return '<a href="javascript:void(0);" class="t-link" style="color: ' . $statusColor . ' !important; font-weight: 600;">' 
                       . $icon . $row['status_label'] . 
                       '</a>';
            })
            ->editColumn('created_at', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['created_at'] . '</a>';
            })
            ->editColumn('invoice_status', function($row) {
                $hasInvoice = strpos($row['invoice_status'], 'Mevcut') !== false;
                
                $invoiceColor = $hasInvoice ? '#17a2b8' : '#fd7e14';
                $icon = $hasInvoice 
                    ? '<i class="fas fa-check-circle me-1"></i>' 
                    : '<i class="fas fa-clock me-1"></i>';
                
                $text = $hasInvoice ? 'Mevcut' : 'Bekleniyor';
                
                return '<a href="javascript:void(0);" class="t-link" style="color: ' . $invoiceColor . ' !important; font-weight: 600;">' 
                       . $icon . $text . 
                       '</a>';
            })
            ->addColumn('action', function($row) use ($tenant_id) {
                if ($row['invoice_path']) {
                    return '<a href="' . asset($row['invoice_path']) . '" 
                            class="btn btn-sm btn-outline-primary mobilBtn" 
                            target="_blank" 
                            title="Faturayı İndir">
                            <i class="fas fa-file-pdf"></i>
                        </a>';
                }
                return '<button class="btn btn-sm btn-outline-secondary mobilBtn btn-disabled-custom" 
                            title="Fatura bekleniyor - Oluşturulduğunda buradan indirebilirsiniz"
                            onclick="return false;">
                            <i class="fas fa-clock"></i>
                        </button>';
            })
                ->rawColumns(['id', 'type_label', 'description', 'amount', 'status_label', 'invoice_status', 'created_at', 'action'])
                ->make(true);
                }

    // Normal sayfa yüklemesi
    $paymentMethods = $this->getPaymentMethods($tenant);
    $statuses = [
        'active' => 'Aktif',
        'completed' => 'Tamamlandı',
        'pending' => 'Beklemede',
        'cancelled' => 'İptal Edildi',
        'expired' => 'Süresi Doldu',
        'failed' => 'Başarısız'
    ];

    $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    $type = $request->get('type', 'all');

    return view('frontend.secure.payment_history.history_index', compact(
        'tenant',
        'paymentMethods',
        'statuses',
        'dateFrom',
        'dateTo',
        'type'
    ));
}

    public function downloadInvoice($type, $id, $tenant_id)
{
    abort_if(!auth()->user()->hasRole('Patron'), 403);

    $tenant = Tenant::findOrFail($tenant_id);
    
    if ($type === 'subscription') {
        $payment = $tenant->subscriptionPayments()->findOrFail($id);
    } elseif ($type === 'integration') {
        $payment = $tenant->integrationPurchases()->findOrFail($id);
    } else {
        $payment = $tenant->storagePurchases()->findOrFail($id);
    }

    if (!$payment->invoice_path) {
        abort(404, 'Fatura yolu bulunamadı');
    }

    // Debug için log ekle
    \Log::info('Invoice download attempt', [
        'payment_id' => $payment->id,
        'invoice_path' => $payment->invoice_path,
        'type' => $type
    ]);

    // Farklı path kombinasyonlarını dene
    $possiblePaths = [
        public_path($payment->invoice_path),
        public_path('upload/uploads/' . basename($payment->invoice_path)),
        storage_path('app/public/' . $payment->invoice_path),
        storage_path('app/' . $payment->invoice_path)
    ];

    $validPath = null;
    foreach ($possiblePaths as $path) {
        \Illuminate\Support\Facades\Log::info('Checking path: ' . $path . ' - Exists: ' . (file_exists($path) ? 'YES' : 'NO'));
        if (file_exists($path)) {
            $validPath = $path;
            break;
        }
    }

    if (!$validPath) {
        \Illuminate\Support\Facades\Log::error('Invoice file not found', [
            'payment_id' => $payment->id,
            'invoice_path' => $payment->invoice_path,
            'checked_paths' => $possiblePaths
        ]);
        abort(404, 'Fatura dosyası bulunamadı: ' . $payment->invoice_path);
    }

    return response()->download(
        $validPath,
        'fatura_' . $payment->id . '.pdf'
    );
}

    private function getSubscriptionPaymentDescription($payment)
    {
        $description = 'Abonelik Ödemesi';
        
        if (!empty($payment->subscription_id)) {
            $description .= " (Abonelik ID: {$payment->subscription_id})";
        }
        
        if (!empty($payment->transaction_id)) {
            $description .= " - İşlem: {$payment->transaction_id}";
        }
        
        if (!empty($payment->gateway)) {
            $description .= " via {$payment->gateway}";
        }
        
        return $description;
    }

    private function getStorageDescription($purchase)
    {
        return "Ek Depolama Alanı - " . ($purchase->storage_gb ?? 0) . " GB";
    }

    private function getIntegrationDescription($purchase)
    {
        $integrationName = $purchase->integration->name ?? 'Bilinmeyen Entegrasyon';
        return "Entegrasyon - " . $integrationName;
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'active' => 'Aktif',
            'completed' => 'Tamamlandı',
            'pending' => 'Beklemede',
            'cancelled' => 'İptal Edildi',
            'expired' => 'Süresi Doldu',
            'failed' => 'Başarısız',
            'paid' => 'Ödendi'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    private function extractPaymentMethod($paymentResponse)
{
    if (is_string($paymentResponse)) {
        $paymentResponse = json_decode($paymentResponse, true);
    }

    if (is_array($paymentResponse)) {
        // payment_type field'ını kontrol et (JSON'dan)
        if (isset($paymentResponse['payment_type'])) {
            return $this->formatPaymentType($paymentResponse['payment_type']);
        }
        
        // Fallback olarak payment_method kontrol et
        if (isset($paymentResponse['payment_method'])) {
            return $this->formatPaymentType($paymentResponse['payment_method']);
        }
    }

    return 'Belirtilmemiş';
}
private function formatPaymentType($paymentType)
{
    $types = [
        'card' => 'Kredi Kartı',
        'credit_card' => 'Kredi Kartı',
        'bank_transfer' => 'Banka Havalesi',
        'eft' => 'EFT',
        'cash' => 'Nakit',
        'paytr' => 'PayTR',
        'iyzico' => 'Iyzico'
    ];

    return $types[$paymentType] ?? ucfirst(str_replace('_', ' ', $paymentType));
}

    public function export(Request $request, $tenant_id)
{
    abort_if(!auth()->user()->hasRole('Patron'), 403);

    $tenant = Tenant::where('id', $tenant_id)->first();
    
    // Aynı filtreleme mantığını kullan
    $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    $paymentMethod = $request->get('payment_method');
    $status = $request->get('status');
    $type = $request->get('type', 'all');

    // Abonelik ödemeleri
    $subscriptionPayments = collect();
    if (method_exists($tenant, 'subscriptionPayments')) {
        try {
            $subscriptionPayments = $tenant->subscriptionPayments()
                ->where('status', 'completed')
                ->when($dateFrom, function($query) use ($dateFrom) {
                    return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                })
                ->when($dateTo, function($query) use ($dateTo) {
                    return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                })
                ->when($paymentMethod, function($query) use ($paymentMethod) {
                    return $query->where('payment_method', $paymentMethod);
                })
                ->when($status, function($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($type && $type !== 'all', function($query) use ($type) {
                    if ($type === 'storage' || $type === 'integration') {
                        return $query->whereRaw('1=0');
                    }
                    return $query;
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'type_label' => 'Abonelik',
                        'description' => $this->getSubscriptionPaymentDescription($payment),
                        'amount' => number_format($payment->amount ?? 0, 2),
                        'currency' => $payment->currency ?? 'TL',
                        'payment_method' => $payment->payment_method ?: 'Belirtilmemiş',
                        'status_label' => $this->getStatusLabel($payment->status),
                        'created_at' => $payment->created_at->format('d.m.Y H:i'),
                        'paid_at' => $payment->paid_at ? $payment->paid_at->format('d.m.Y H:i') : '-',
                        'transaction_id' => $payment->transaction_id ?: '-',
                        'gateway' => $payment->gateway ?: '-',
                        'has_invoice' => !empty($payment->invoice_path) && file_exists(storage_path('app/' . $payment->invoice_path)) ? 'Mevcut' : 'Bekleniyor'
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Subscription payments export error: ' . $e->getMessage());
            $subscriptionPayments = collect();
        }
    }

    // Depolama satın almaları
    $storagePurchases = collect();
    if (method_exists($tenant, 'storagePurchases')) {
        try {
            $storagePurchases = $tenant->storagePurchases()
                ->where('status', 'completed')
                ->when($dateFrom, function($query) use ($dateFrom) {
                    return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                })
                ->when($dateTo, function($query) use ($dateTo) {
                    return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                })
                ->when($paymentMethod, function($query) use ($paymentMethod) {
                    // Formatlanmış payment method ile karşılaştır
                    return $query->where(function($q) use ($paymentMethod) {
                        $q->whereJsonContains('payment_response->payment_type', 'card')
                          ->orWhere('payment_method', $paymentMethod)
                          ->orWhere('payment_type', $paymentMethod);
                    });
                })
                ->when($status, function($query) use ($status) {
                    return $query->where('status', $status)
                                 ->orWhereJsonContains('payment_response->status', $status);
                })
                ->when($type && $type !== 'all', function($query) use ($type) {
                    if ($type === 'subscription' || $type === 'integration') {
                        return $query->whereRaw('1=0');
                    }
                    return $query;
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($purchase) {
                    $paymentResponse = is_string($purchase->payment_response) 
                        ? json_decode($purchase->payment_response, true) 
                        : ($purchase->payment_response ?? []);
                        
                    return [
                        'id' => $purchase->id,
                        'type_label' => 'Depolama',
                        'description' => $this->getStorageDescription($purchase),
                        'amount' => number_format($purchase->amount ?? 0, 2),
                        'currency' => $paymentResponse['currency'] ?? 'TL',
                        'payment_method' => $this->formatPaymentType($paymentResponse['payment_type'] ?? 'Belirtilmemiş'),
                        'status_label' => $this->getStatusLabel($purchase->status),
                        'created_at' => $purchase->created_at->format('d.m.Y H:i'),
                        'paid_at' => isset($purchase->purchased_at) ? $purchase->purchased_at->format('d.m.Y H:i') : '-',
                        'transaction_id' => $paymentResponse['merchant_oid'] ?? ($purchase->payment_token ?? '-'),
                        'gateway' => isset($paymentResponse['payment_type']) ? 'PayTR' : 'Depolama Sistemi',
                        'has_invoice' => !empty($purchase->invoice_path) && file_exists(storage_path('app/' . $purchase->invoice_path)) ? 'Mevcut' : 'Bekleniyor'
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Storage purchases export error: ' . $e->getMessage());
            $storagePurchases = collect();
        }
    }

    // Entegrasyon satın almaları
    $integrationPurchases = collect();
    if (method_exists($tenant, 'integrationPurchases')) {
        try {
            $integrationPurchases = $tenant->integrationPurchases()
                ->with('integration')
                ->where('status', 'completed')
                ->when($dateFrom, function($query) use ($dateFrom) {
                    return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                })
                ->when($dateTo, function($query) use ($dateTo) {
                    return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                })
                ->when($paymentMethod, function($query) use ($paymentMethod) {
                    return $query->where(function($q) use ($paymentMethod) {
                        $q->whereJsonContains('payment_response->payment_type', 'card')
                          ->orWhere('payment_method', $paymentMethod)
                          ->orWhere('payment_type', $paymentMethod);
                    });
                })
                ->when($status, function($query) use ($status) {
                    return $query->where('status', $status)
                                 ->orWhereJsonContains('payment_response->status', $status);
                })
                ->when($type && $type !== 'all', function($query) use ($type) {
                    if ($type === 'subscription' || $type === 'storage') {
                        return $query->whereRaw('1=0');
                    }
                    return $query;
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($purchase) {
                    $paymentResponse = is_string($purchase->payment_response) 
                        ? json_decode($purchase->payment_response, true) 
                        : ($purchase->payment_response ?? []);
                        
                    return [
                        'id' => $purchase->id,
                        'type_label' => 'Entegrasyon',
                        'description' => $this->getIntegrationDescription($purchase),
                        'amount' => number_format($purchase->amount ?? 0, 2),
                        'currency' => $paymentResponse['currency'] ?? 'TL',
                        'payment_method' => $this->formatPaymentType($paymentResponse['payment_type'] ?? 'Belirtilmemiş'),
                        'status_label' => $this->getStatusLabel($purchase->status),
                        'created_at' => $purchase->created_at->format('d.m.Y H:i'),
                        'paid_at' => isset($purchase->purchased_at) ? $purchase->purchased_at->format('d.m.Y H:i') : '-',
                        'transaction_id' => $paymentResponse['merchant_oid'] ?? ($purchase->payment_token ?? '-'),
                        'gateway' => isset($paymentResponse['payment_type']) ? 'PayTR' : 'Entegrasyon Sistemi',
                        'has_invoice' => !empty($purchase->invoice_path) && file_exists(storage_path('app/' . $purchase->invoice_path)) ? 'Mevcut' : 'Bekleniyor'
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Integration purchases export error: ' . $e->getMessage());
            $integrationPurchases = collect();
        }
    }

    // Üç collection'ı birleştir
    $payments = $subscriptionPayments
        ->concat($storagePurchases)
        ->concat($integrationPurchases)
        ->sortByDesc('created_at')
        ->values();

    // CSV olarak export et
    $filename = 'odeme-gecmisi-' . ($tenant->name ?? 'tenant') . '-' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ];

    $callback = function() use ($payments) {
        $file = fopen('php://output', 'w');
        
        // BOM for UTF-8 Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($file, [
            'ID',
            'Tür', 
            'Açıklama',
            'Tutar',
            'Para Birimi',
            'Ödeme Yöntemi',
            'Durum',
            'Oluşturma Tarihi',
            'Ödeme Tarihi',
            'İşlem ID',
            'Gateway',
            'Fatura Durumu'
        ], ';');

        // Data
        foreach ($payments as $payment) {
            try {
                fputcsv($file, [
                    $payment['id'] ?? '',
                    $payment['type_label'] ?? '',
                    $payment['description'] ?? '',
                    $payment['amount'] ?? '0,00',
                    $payment['currency'] ?? 'TL',
                    $payment['payment_method'] ?? 'Belirtilmemiş',
                    $payment['status_label'] ?? '',
                    $payment['created_at'] ?? '',
                    $payment['paid_at'] ?? '-',
                    $payment['transaction_id'] ?? '-',
                    $payment['gateway'] ?? '-',
                    $payment['has_invoice'] ?? 'Bekleniyor'
                ], ';');
            } catch (\Exception $e) {
                \Log::error('CSV row error: ' . $e->getMessage());
                // Hatalı satırı atla, devam et
                continue;
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/**
 * Depolama ödemeleri için ödeme yöntemi extraction'ı
 */
private function extractPaymentMethodForExport($purchase)
{
    // Önce doğrudan field'leri kontrol et
    if (!empty($purchase->payment_method)) {
        return $purchase->payment_method;
    }
    
    if (!empty($purchase->payment_type)) {
        return $purchase->payment_type;
    }
    
    // Sonra JSON response'u kontrol et
    if (!empty($purchase->payment_response)) {
        $paymentResponse = is_string($purchase->payment_response) 
            ? json_decode($purchase->payment_response, true) 
            : $purchase->payment_response;
        
        if (is_array($paymentResponse)) {
            if (isset($paymentResponse['payment_method'])) {
                return $paymentResponse['payment_method'];
            }
            if (isset($paymentResponse['payment_type'])) {
                return $paymentResponse['payment_type'];
            }
        }
    }
    
    return 'Belirtilmemiş';
}
private function extractPaymentMethodUnified($purchase)
{
    // Önce doğrudan field'leri kontrol et
    if (!empty($purchase->payment_method)) {
        return $this->formatPaymentType($purchase->payment_method);
    }
    
    if (!empty($purchase->payment_type)) {
        return $this->formatPaymentType($purchase->payment_type);
    }
    
    // JSON response'u kontrol et
    if (!empty($purchase->payment_response)) {
        $paymentResponse = is_string($purchase->payment_response) 
            ? json_decode($purchase->payment_response, true) 
            : $purchase->payment_response;
        
        if (is_array($paymentResponse)) {
            if (isset($paymentResponse['payment_type'])) {
                return $this->formatPaymentType($paymentResponse['payment_type']);
            }
            if (isset($paymentResponse['payment_method'])) {
                return $this->formatPaymentType($paymentResponse['payment_method']);
            }
        }
    }
    
    return 'Belirtilmemiş';
}
    private function getPaymentMethods($tenant)
{
    $methods = collect();

    // Abonelik ödeme yöntemlerini al
    if (method_exists($tenant, 'subscriptionPayments')) {
        $subscriptionMethods = $tenant->subscriptionPayments()
            ->whereNotNull('payment_method')
            ->pluck('payment_method')
            ->unique()
            ->map(function($method) {
                return $this->formatPaymentType($method);
            });
        $methods = $methods->concat($subscriptionMethods);
    }

    // Depolama satın alma ödeme yöntemlerini al
    if (method_exists($tenant, 'storagePurchases')) {
        $storagePurchases = $tenant->storagePurchases()
            ->whereNotNull('payment_response')
            ->get();
            
        $storageMethods = $storagePurchases->map(function($purchase) {
            return $this->extractPaymentMethodUnified($purchase);
        })->filter()->unique();
        
        $methods = $methods->concat($storageMethods);
    }

    // Entegrasyon satın alma ödeme yöntemlerini al
    if (method_exists($tenant, 'integrationPurchases')) {
        $integrationPurchases = $tenant->integrationPurchases()
            ->whereNotNull('payment_response')
            ->get();
            
        $integrationMethods = $integrationPurchases->map(function($purchase) {
            return $this->extractPaymentMethodUnified($purchase);
        })->filter()->unique();
        
        $methods = $methods->concat($integrationMethods);
    }

    return $methods->unique()->sort()->values();
}

}