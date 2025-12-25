<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Il;
use App\Models\IntegrationPurchase;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\PaytrService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    protected $paytrService;

    public function __construct(SubscriptionService $subscriptionService, PaytrService $paytrService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->paytrService = $paytrService;
    }

    /**
     * Abonelik paketlerini göster
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        $tenant = Auth::user()->tenant;
        $currentPlan = $tenant->plan();
        $onTrial = $tenant->isOnTrial();
        $remainingTrialDays = $tenant->getRemainingTrialDays();
        return view('frontend.secure.subscription.plans', compact('plans', 'tenant', 'currentPlan','onTrial',
        'remainingTrialDays'));
    }

    public function subscriptionPlans($tenant_id, Request $request) {
        $tenant = Tenant::findOrFail($tenant_id);
        $currentPlan = $tenant->plan();
        
        $allPlans = SubscriptionPlan::where('is_active', true)
                            ->where('price', '>', $currentPlan?->price ?? 0)
                            ->orderBy('price', 'asc')
                            ->get();
    
    // Eğer belirli bir feature için filtreleme isteniyorsa
    if ($request->has('feature')) {
        $plans = $allPlans->filter(function($plan) use ($request) {
            return $plan->hasFeature($request->feature);
        });
    } else {
        $plans = $allPlans;
    }

            return view('frontend.secure.subscription.all_plans', compact('plans','tenant','currentPlan'));
    }

    /**
     * Abonelik satın alma formu
     */
    public function subscribe($tenant_id, $planid)
    {
        $tenant = Auth::user()->tenant;
        $plan = SubscriptionPlan::where('id', $planid)->first();
        
        // Zaten aynı plana sahipse
        if ($tenant->currentSubscription && $tenant->currentSubscription->plan_id == $plan->id) {
            return redirect()->route('subscription.plans')
                           ->with('info', 'Zaten bu paketi kullanıyorsunuz.');
        }
        
        $countries = Il::orderBy('name', 'ASC')->get();
        return view('frontend.secure.subscription.checkout', compact('plan', 'tenant', 'countries', 'tenant_id', 'planid'));
    }

    public function processSubscription(Request $request, $tenant_id, $planid)
    {
        $plan = SubscriptionPlan::findOrFail($planid);
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'billing_type' => 'required|in:bireysel,kurumsal',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'il' => 'required|string',
            'ilce' => 'required|string',
            'neighborhood' => 'nullable|string',
            'address' => 'nullable|string',
            'identity_number' => 'nullable|string',
            'foreign' => 'nullable|boolean',
            'tax_office' => 'nullable|string',
            'tax_number' => 'nullable|string',
        ]);

        // Paket ve form bilgilerini session'a kaydet
        session([
            'subscription.plan' => $plan->toArray(),
            'subscription.billing' => $validated,
            'subscription.tenant_id' => $tenant_id,
            'subscription.planid' => $planid
        ]);

        // Ödeme sayfasına yönlendir
        return redirect()->route('subscription.payment', [$tenant_id, $planid]);
    }

    public function payment($tenant_id, $planid)
    {
        // Session'daki verileri al
        $planData = session('subscription.plan');
        $billingData = session('subscription.billing');

        // Eğer session boşsa checkout sayfasına geri yönlendir
        if (!$planData || !$billingData) {
            return redirect()->route('subscription.subscribe', [$tenant_id, $planid])
                            ->with('error', 'Ödeme adımına geçmek için önce formu doldurmalısınız.');
        }

        $tenant = Auth::user()->tenant;
        $plan = SubscriptionPlan::findOrFail($planid);

        // Blade dosyasına verileri gönder
        return view('frontend.secure.subscription.payment', [
            'planData' => $planData,
            'billingData' => $billingData,
            'tenant_id' => $tenant_id,
            'planid' => $planid,
            'tenant' => $tenant,
            'plan' => $plan
        ]);
    }

    /**
     * Paytr ödeme işlemini başlat
     */
    /**
 * Paytr ödeme işlemini başlat - Debug versiyonu
 */
public function initiatePayment(Request $request, $tenant_id, $planid)
{
    try {
        // Debug: Method çağrıldığını log'la
        Log::info('initiatePayment called', [
            'tenant_id' => $tenant_id,
            'planid' => $planid,
            'request_data' => $request->all()
        ]);

        // Session verilerini kontrol et
        $planData = session('subscription.plan');
        $billingData = session('subscription.billing');

        // Debug: Session verilerini kontrol et
        Log::info('Session data check', [
            'planData' => $planData,
            'billingData' => $billingData
        ]);

        if (!$planData || !$billingData) {
            Log::error('Session data missing');
            return redirect()->route('subscription.subscribe', [$tenant_id, $planid])
                            ->with('error', 'Ödeme bilgileri bulunamadı. Lütfen tekrar deneyin.');
        }

        // Config değerlerini kontrol et
        $merchantId = config('paytr.merchant_id');
        $merchantKey = config('paytr.merchant_key');
        $merchantSalt = config('paytr.merchant_salt');

        Log::info('Paytr Config Check', [
            'merchant_id' => $merchantId,
            'merchant_key' => $merchantKey ? 'SET' : 'NOT SET',
            'merchant_salt' => $merchantSalt ? 'SET' : 'NOT SET'
        ]);

        if (!$merchantId || !$merchantKey || !$merchantSalt) {
            Log::error('Paytr config missing');
            return back()->with('error', 'Ödeme sistemi yapılandırma hatası. Lütfen sistem yöneticisiyle iletişime geçin.');
        }

        $plan = SubscriptionPlan::findOrFail($planid);
        $tenant = Auth::user()->tenant;

        // Benzersiz sipariş ID oluştur
        $orderId = 'SUB' . $tenant_id . '' . $planid . '' . time();
        
        // KDV dahil toplam tutarı hesapla
        $totalAmount = $plan->price * 1.20;

        // Sepet bilgilerini hazırla
        $basket = [
            [
                $plan->name . ' - Abonelik Paketi',
                number_format($plan->price, 2, '.', ''),
                1
            ]
        ];

        // Paytr için sipariş verilerini hazırla
        $orderData = [
            'tenant' => $tenant->id,
            'order_id' => $orderId,
            'email' => $billingData['email'],
            'amount' => $totalAmount,
            'user_name' => $billingData['first_name'],
            'user_address' => $billingData['address'] ?? $billingData['il'] . '/' . $billingData['ilce'],
            'user_phone' => $billingData['phone'],
            'basket' => $basket,
            'success_url' => route('subscription.payment.success'),
            'fail_url' => route('subscription.payment.fail'),
        ];

        Log::info('Order data prepared', $orderData);

        // Ödeme kaydını pending olarak oluştur
        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant_id,
            'payment_id' => $orderId,
            'amount' => $totalAmount,
            'currency' => 'TL',
            'status' => 'pending',
            'payment_method' => 'Kredi Kartı',
            'gateway' => 'Paytr',
            'gateway_response' => json_encode(['order_data' => $orderData,
        'billing' => $billingData,
        'plan' => $planData]),
        ]);

        Log::info('Payment record created', ['payment_id' => $payment->id]);

        // Session'a payment ID'sini kaydet
        session(['subscription.payment_id' => $payment->id]);

        // Paytr iframe token'ını al
        Log::info('Calling Paytr API...');
        $paytrResponse = $this->paytrService->createPaymentIframe($orderData);

        Log::info('Paytr API Response', $paytrResponse);

        if ($paytrResponse['success']) {
            Log::info('Paytr success, redirecting to iframe');
            
            return view('frontend.secure.subscription.paytr_payment', [
                'iframe_url' => $paytrResponse['iframe_url'],
                'planData' => $planData,
                'billingData' => $billingData,
                'totalAmount' => $totalAmount,
                'tenant_id' => $tenant_id,
                'planid' => $planid
            ]);
        } else {
            Log::error('Paytr API Error', $paytrResponse);
            
            // Payment kaydını başarısız olarak güncelle
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $paytrResponse['error']
            ]);

            return back()->with('error', 'Ödeme servisiyle bağlantı kurulamadı: ' . $paytrResponse['error']);
        }

    } catch (\Exception $e) {
        Log::error('Payment initiation error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', 'Ödeme başlatılırken bir hata oluştu: ' . $e->getMessage());
    }
}

    /**
     * Paytr callback (ödeme bildirimi)
     */
    public function paymentCallback(Request $request)
{
    try {
        $postData = $request->all();
        Log::info('Paytr Callback Received:', $postData);

        // Callback'i doğrula
        if (!$this->paytrService->verifyCallback($postData)) {
            Log::error('Paytr callback verification failed', $postData);
            return response('FAIL', 200);
        }

        Log::info('Callback verification successful');

        $merchant_oid = $postData['merchant_oid'];

        // Ödeme türünü merchant_oid prefix'inden belirle
        if (str_starts_with($merchant_oid, 'ST')) {
            // Storage ödemesi
            return $this->handleStoragePayment($postData);
        } elseif (str_starts_with($merchant_oid, 'INT')) {
            // Entegrasyon ödemesi
            return $this->handleIntegrationPayment($postData);
        } else {
            // Mevcut abonelik ödeme işlemi
            return $this->handleSubscriptionPayment($postData);
        }

    } catch (\Exception $e) {
        Log::error('Payment callback error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'post_data' => $request->all()
        ]);
        return response('FAIL', 200);
    }
}

    private function handleStoragePayment($postData)
    {
        // Storage satın alımını bul
        $purchase = \App\Models\StoragePurchase::where('payment_token', $postData['merchant_oid'])->first();

        // Ödeme zaten işlenmişse
        if ($purchase->status !== 'pending') {
            Log::info('Storage payment already processed: ' . $postData['merchant_oid']);
            return response('OK', 200);
        }

        // Ödeme durumunu güncelle
        if ($postData['status'] == 'success') {
            $purchase->update([
                'status' => 'completed',
                'purchased_at' => now(),
                'payment_response' => $postData
            ]);

            ActivityLogger::log('storage_purchased', "Ek depolama satın alındı: +{$purchase->storage_gb} GB ({$purchase->package->name})", [
                'module' => 'storage',
                'reference_table' => 'storage_purchases',
                'reference_id' => $purchase->id,
                'tenant_id' => $purchase->tenant_id, 
                'new_values' => [
                    'storage_gb' => $purchase->storage_gb,
                    'amount' => $purchase->amount,
                    'package_name' => $purchase->package->name ?? 'Storage Paketi',
                    'status' => 'completed'
                ]
            ]);

            // Başarı mesajını session'a kaydet
            session()->flash('storage_payment_success', [
                'message' => 'Ödeme başarılı! +' . $purchase->storage_gb . ' GB ek depolama alanınız hesabınıza eklendi.',
                'tenant_id' => $purchase->tenant_id,
                'package_name' => $purchase->package->name ?? 'Storage Paketi'
            ]);

            Log::info('Storage payment completed successfully: ' . $postData['merchant_oid'], [
                'tenant_id' => $purchase->tenant_id,
                'storage_gb' => $purchase->storage_gb,
                'amount' => $purchase->amount
            ]);

            return response('OK', 200);
            
        } else {
            $purchase->update([
                'status' => 'failed',
                'payment_response' => $postData
            ]);

        ActivityLogger::log('storage_purchase_failed', "Depolama satın alma başarısız: {$purchase->storage_gb} GB - " . ($postData['failed_reason_msg'] ?? 'Bilinmeyen hata'), [
            'module' => 'storage',
            'reference_table' => 'storage_purchases',
            'reference_id' => $purchase->id,
            'tenant_id' => $purchase->tenant_id, 
            'new_values' => [
                'storage_gb' => $purchase->storage_gb,
                'amount' => $purchase->amount,
                'status' => 'failed',
                'failure_reason' => $postData['failed_reason_msg'] ?? 'Bilinmeyen hata'
            ]
        ]);


            // Hata mesajını session'a kaydet
            session()->flash('storage_payment_error', [
                'message' => 'Storage ödeme işlemi başarısız.',
                'tenant_id' => $purchase->tenant_id,
                'reason' => $postData['failed_reason_msg'] ?? 'Bilinmeyen hata'
            ]);

            Log::warning('Storage payment failed: ' . $postData['merchant_oid'], [
                'reason' => $postData['failed_reason_msg'] ?? 'No reason provided'
            ]);
            return response('OK', 200);
        }
    }


    private function handleIntegrationPayment($postData)
{
    try {
        $merchant_oid = $postData['merchant_oid'];
        
        Log::info('Searching for integration purchase', [
            'merchant_oid' => $merchant_oid
        ]);
        
        // Entegrasyon satın alımını bul
        $purchase = IntegrationPurchase::where('tokenPayment', $merchant_oid)->first();

        // Purchase bulunamazsa bile OK dön (sonsuz loop engellemek için)
        if (!$purchase) {
            Log::error('Integration purchase NOT FOUND - Returning OK to stop retry loop', [
                'merchant_oid' => $merchant_oid,
                'recent_5_purchases' => IntegrationPurchase::latest()
                    ->take(5)
                    ->get(['id', 'tokenPayment', 'tenant_id', 'status'])
                    ->toArray()
            ]);
            
            // ÖNEMLİ: Purchase bulunamasa bile OK dön ki PayTR tekrar denemesin
            return response('OK', 200);
        }
        
        Log::info('Integration purchase FOUND', [
            'id' => $purchase->id,
            'tokenPayment' => $purchase->tokenPayment,
            'current_status' => $purchase->status
        ]);

        // Ödeme zaten işlenmişse
        if ($purchase->status !== 'pending') {
            Log::info('Integration payment already processed - returning OK', [
                'merchant_oid' => $merchant_oid,
                'status' => $purchase->status
            ]);
            return response('OK', 200);
        }

        // Ödeme durumunu güncelle
        if ($postData['status'] == 'success') {
            // Entegrasyon bilgilerini yükle
            $integration = $purchase->integration;
            
            // Güncelleme verilerini hazırla
            $updateData = [
                'status' => 'completed',
                'paid_at' => now(),
                'is_active' => true,
                'activated_at' => now(),
                'transaction_id' => $postData['payment_id'] ?? $merchant_oid,
                'gateway' => 'paytr',
                'payment_method' => 'credit_card',
                'payment_response' => $postData
            ];
            
            // Eğer Hipcall entegrasyonu ise webhook token ve URL oluştur
            if ($integration && $integration->slug === 'hipcall') {
                // Webhook token yoksa oluştur
                if (!$purchase->webhook_token) {
                    $webhookToken = Str::random(32);
                    $webhookUrl = url('/api/webhook/hipcall/' . $webhookToken);
                    
                    $updateData['webhook_token'] = $webhookToken;
                    $updateData['webhook_url'] = $webhookUrl;
                    
                    Log::info('Hipcall webhook credentials created', [
                        'purchase_id' => $purchase->id,
                        'webhook_url' => $webhookUrl
                    ]);
                    ActivityLogger::log('hipcall_webhook_created', "Hipcall webhook oluşturuldu", [
                        'module' => 'integration',
                        'reference_table' => 'integration_purchases',
                        'reference_id' => $purchase->id,
                        'tenant_id' => $purchase->tenant_id,
                        'new_values' => [
                            'webhook_url' => $webhookUrl,
                            'created_at' => now()->toDateTimeString()
                        ]
                    ]);
                }
            }
            
            // Purchase'ı güncelle
            $purchase->update($updateData);

            ActivityLogger::log('integration_purchased', "Entegrasyon satın alındı: {$purchase->integration->name}", [
                'module' => 'integration',
                'reference_table' => 'integration_purchases',
                'reference_id' => $purchase->id,
                'tenant_id' => $purchase->tenant_id,
                'new_values' => [
                    'integration_name' => $purchase->integration->name ?? 'Entegrasyon',
                    'amount' => $purchase->amount,
                    'status' => 'completed',
                    'activated_at' => now()->toDateTimeString()
                ]
            ]);

            // Session yerine direkt flag ekle (webhook'ta session çalışmayabilir)
            Cache::put(
                'integration_payment_success_' . $purchase->tenant_id, 
                [
                    'message' => 'Ödeme başarılı! ' . ($purchase->integration->name ?? 'Entegrasyon') . ' aktifleştirildi.',
                    'tenant_id' => $purchase->tenant_id,
                    'integration_id' => $purchase->integration_id,
                    'integration_name' => $purchase->integration->name ?? 'Entegrasyon',
                    'is_hipcall' => $integration->slug === 'hipcall',
                ],
                now()->addMinutes(10)
            );

            Log::info('Integration payment completed successfully', [
                'merchant_oid' => $merchant_oid,
                'purchase_id' => $purchase->id
            ]);

            return response('OK', 200);
            
        } else {
            // Ödeme başarısız veya iptal
            $purchase->update([
                'status' => 'failed',
                'payment_response' => $postData
            ]);

            ActivityLogger::log('integration_purchase_failed', "Entegrasyon satın alma başarısız: {$purchase->integration->name} - " . ($postData['failed_reason_msg'] ?? 'Bilinmeyen hata'), [
                'module' => 'integration',
                'reference_table' => 'integration_purchases',
                'reference_id' => $purchase->id,
                'tenant_id' => $purchase->tenant_id,
                'new_values' => [
                    'integration_name' => $purchase->integration->name ?? 'Entegrasyon',
                    'amount' => $purchase->amount,
                    'status' => 'failed',
                    'failure_reason' => $postData['failed_reason_msg'] ?? 'Bilinmeyen hata'
                ]
            ]);

            Cache::put(
                'integration_payment_error_' . $purchase->tenant_id,
                [
                    'message' => 'Entegrasyon ödeme işlemi başarısız.',
                    'tenant_id' => $purchase->tenant_id,
                    'reason' => $postData['failed_reason_msg'] ?? 'Bilinmeyen hata'
                ],
                now()->addMinutes(10)
            );

            Log::warning('Integration payment failed', [
                'merchant_oid' => $merchant_oid,
                'reason' => $postData['failed_reason_msg'] ?? 'No reason provided',
                'code' => $postData['failed_reason_code'] ?? 'N/A'
            ]);
            
            // ÖNEMLİ: Failed durumda da OK dön
            return response('OK', 200);
        }
        
    } catch (\Exception $e) {
        Log::error('Integration payment handling error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'merchant_oid' => $postData['merchant_oid'] ?? 'unknown'
        ]);
        
        // ÖNEMLİ: Hata durumunda bile OK dön ki sonsuz loop olmasın
        return response('OK', 200);
    }
}

    private function handleSubscriptionPayment($postData)
    {
        // Mevcut abonelik kodunuz - değişiklik yok
        $payment = SubscriptionPayment::where('payment_id', $postData['merchant_oid'])->first();

        if (!$payment) {
            Log::error('Payment not found for order ID: ' . $postData['merchant_oid']);
            return response('OK', 200);
        }

        if ($payment->status !== 'pending') {
            Log::info('Payment already processed: ' . $postData['merchant_oid']);
            return response('OK', 200);
        }

        if ($postData['status'] == 'success') {
            $existingResponse = json_decode($payment->gateway_response, true) ?? [];
            $existingResponse['callback'] = $postData;
            
            $payment->update([
                'status' => 'completed',
                'transaction_id' => $postData['transaction_id'] ?? null,
                'gateway_response' => json_encode($existingResponse),
                'paid_at' => Carbon::now(),
            ]);
        ActivityLogger::log('payment_success', "Ödeme başarıyla tamamlandı: {$payment->amount} TL ({$payment->payment_method})", [
            'module' => 'payment',
            'reference_table' => 'subscription_payments',
            'reference_id' => $payment->id,
            'tenant_id' => $payment->tenant_id,
            'new_values' => [
                'order_id' => $postData['merchant_oid'],
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => 'completed',
                'transaction_id' => $postData['transaction_id'] ?? null
            ]
        ]);


            $this->completeSubscription($payment);

            Log::info('Payment completed successfully: ' . $postData['merchant_oid']);
            return response('OK', 200);
            
        } else {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $postData['failed_reason_msg'] ?? 'Ödeme başarısız',
                'gateway_response' => json_encode($postData),
            ]);
        ActivityLogger::log('payment_failed', "Ödeme başarısız oldu: {$payment->amount} TL - " . ($postData['failed_reason_msg'] ?? 'Bilinmeyen hata'), [
            'module' => 'payment',
            'reference_table' => 'subscription_payments',
            'reference_id' => $payment->id,
            'tenant_id' => $payment->tenant_id,
            'new_values' => [
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => 'failed',
                'failure_reason' => $postData['failed_reason_msg'] ?? 'Bilinmeyen hata'
            ]
        ]);
            Log::warning('Payment failed: ' . $postData['merchant_oid'], [
                'reason' => $postData['failed_reason_msg'] ?? 'No reason provided'
            ]);
            return response('OK', 200);
        }
    }

    /**
     * Ödeme başarı sayfası
     */
    public function paymentSuccess()
{
    Log::info('Payment success page accessed');
    
    // Session'dan tenant_id ve planid al
    $tenantId = session('subscription.tenant_id');
    $planId = session('subscription.planid');
    $paymentId = session('subscription.payment_id');
    
    Log::info('Payment success session data:', [
        'tenant_id' => $tenantId,
        'plan_id' => $planId,
        'payment_id' => $paymentId
    ]);
    
    if (!$tenantId || !$planId) {
        Log::error('Session data missing in success page');

        ActivityLogger::log('subscription_payment_error', "Abonelik ödeme başarı sayfası - Session verisi eksik", [
            'module' => 'subscription',
            'reference_table' => 'tenants',
            'reference_id' => null,
            'new_values' => [
                'error' => 'Session data missing',
                'tenant_id' => $tenantId,
                'plan_id' => $planId
            ]
        ]);

        return redirect('/')->with('error', 'Geçersiz ödeme oturumu.');
    }
    
    if ($paymentId) {
        $payment = SubscriptionPayment::find($paymentId);

    
        if ($payment && $payment->status == 'completed') {
            ActivityLogger::log('subscription_payment_success_page', "Abonelik ödeme başarı sayfasına yönlendirildi", [
                'module' => 'subscription',
                'reference_table' => 'subscription_payments',
                'reference_id' => $payment->id,
                'tenant_id' => $tenantId,
                'new_values' => [
                    'message' => 'Ödemeniz başarıyla tamamlandı! Aboneliğiniz aktif edildi.',
                    'amount' => $payment->amount,
                    'payment_id' => $payment->id,
                    'subscription_id' => $payment->subscription_id
                ]
            ]);
            session()->forget('subscription');
            
           
            return redirect()->route('payment-history.index', $tenantId)
                                ->with('success', 'Ödemeniz başarıyla tamamlandı! Aboneliğiniz aktif edildi.');
            
        }
    }
}

    /**
     * Ödeme başarısız sayfası
     */
    public function paymentFail()
    {
        Log::info('Payment fail page accessed');
        
        // Session'dan verileri al
        $tenantId = session('subscription.tenant_id');
        $planId = session('subscription.planid');
        $paymentId = session('subscription.payment_id');
        
        if (!$tenantId || !$planId) {
            Log::error('Session data missing in fail page');

            ActivityLogger::log('subscription_payment_error', "Abonelik ödeme hata sayfası - Session verisi eksik", [
                'module' => 'subscription',
                'reference_table' => 'tenants',
                'reference_id' => null,
                'new_values' => [
                    'error' => 'Session data missing in fail page',
                    'tenant_id' => $tenantId,
                    'plan_id' => $planId
                ]
            ]);

            return redirect()->route('subscription.plans')
                            ->with('error', 'Geçersiz ödeme oturumu.');
        }
        
        $errorMessage = 'Ödeme işlemi başarısız oldu.';
        
        if ($paymentId) {
            $payment = SubscriptionPayment::find($paymentId);
            if ($payment && $payment->failure_reason) {
                $errorMessage = $payment->failure_reason;
            }
             ActivityLogger::log('subscription_payment_fail_page', "Abonelik ödeme başarısız - Kullanıcı hata sayfasına yönlendirildi: " . ($failureReason ?? 'Bilinmeyen hata'), [
            'module' => 'subscription',
            'reference_table' => 'subscription_payments',
            'reference_id' => $payment ? $payment->id : null,
            'tenant_id' => $tenantId,
            'new_values' => [
                'message' => $errorMessage,
                'failure_reason' => $failureReason,
                'payment_id' => $paymentId,
                'plan_id' => $planId
            ]
        ]);
        }

        return redirect()->route('subscription.payment', [$tenantId, $planId])
                        ->with('error', 'Ödeme başarısız!');
    }

    /**
     * Abonelik işlemlerini tamamla
     */
    private function completeSubscription(SubscriptionPayment $payment)
    {
        try {
        Log::info('Starting subscription completion', [
            'payment_id' => $payment->id,
            'tenant_id' => $payment->tenant_id
        ]);

        DB::beginTransaction();

        $gatewayResponse = json_decode($payment->gateway_response, true);

    $billingData = $gatewayResponse['billing'] ?? null;
    $planData = $gatewayResponse['plan'] ?? null;
    
    if (!$billingData || !$planData) {
        throw new \Exception('Billing veya plan bilgisi bulunamadı (gateway_response eksik).');
    }

        Log::info('Session data retrieved', [
            'billing_data_exists' => !is_null($billingData),
            'plan_data_exists' => !is_null($planData),
            'billing_keys' => $billingData ? array_keys($billingData) : [],
            'plan_keys' => $planData ? array_keys($planData) : []
        ]);

        if (!$billingData || !$planData) {
            Log::error('Session data missing in completeSubscription', [
                'billing_data' => $billingData,
                'plan_data' => $planData
            ]);
            throw new \Exception('Session verisi bulunamadı - billing: ' . (!$billingData ? 'missing' : 'ok') . ', plan: ' . (!$planData ? 'missing' : 'ok'));
        }

        // Tenant'ı bul
        $tenant = Tenant::find($payment->tenant_id);
        if (!$tenant) {
            throw new \Exception('Tenant bulunamadı: ' . $payment->tenant_id);
        }
        
        Log::info('Tenant found', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name ?? 'N/A'
        ]);

        // Plan'ı bul
        $plan = SubscriptionPlan::find($planData['id']);
        if (!$plan) {
            throw new \Exception('Plan bulunamadı: ' . $planData['id']);
        }

        Log::info('Plan found', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'billing_cycle' => $plan->billing_cycle
        ]);

        // Abonelik tarihleri
        $startDate = Carbon::now();
        $endDate = $plan->billing_cycle == 'yearly' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();

        Log::info('Subscription dates calculated', [
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
            'billing_cycle' => $plan->billing_cycle
        ]);

        // Tenant güncelleme verilerini hazırla
        $tenantUpdateData = [
            'subscription_status' => 'active',
            'status' => 1,
            'bitisTarihi' => $endDate,
            'trial_used' => 1,
            'subscription_ends_at' => $endDate,
            'name' => $billingData['first_name'],
            'eposta' => $billingData['email'],
            'tel1' => $billingData['phone'],
            'il' => $billingData['il'],
            'ilce' => $billingData['ilce'],
            'updated_at' => Carbon::now()
        ];

        // Opsiyonel alanları ekle
        if (!empty($billingData['identity_number'])) {
            $tenantUpdateData['tcNo'] = $billingData['identity_number'];
        }
        if (!empty($billingData['tax_office'])) {
            $tenantUpdateData['vergiDairesi'] = $billingData['tax_office'];
        }
        if (!empty($billingData['tax_number'])) {
            $tenantUpdateData['vergiNo'] = $billingData['tax_number'];
        }
        if (!empty($billingData['address'])) {
            $tenantUpdateData['adres'] = $billingData['address'];
        }

        Log::info('Tenant update data prepared', $tenantUpdateData);

        // Tenant'ı güncelle
        $tenantUpdated = $tenant->update($tenantUpdateData);
        
        Log::info('Tenant update result', [
            'success' => $tenantUpdated,
            'tenant_id' => $tenant->id
        ]);

        if (!$tenantUpdated) {
            throw new \Exception('Tenant güncellenemedi');
        }

        // Tenant'ı yeniden yükle ve kontrol et
        $tenant->refresh();
        Log::info('Tenant after update', [
            'subscription_status' => $tenant->subscription_status,
            'status' => $tenant->status,
            'bitisTarihi' => $tenant->bitisTarihi,
            'subscription_ends_at' => $tenant->subscription_ends_at
        ]);

        // Mevcut abonelikleri iptal et
        $canceledCount = TenantSubscription::where('tenant_id', $payment->tenant_id)
                                          ->where('status', '!=', 'canceled')
                                          ->update([
                                              'status' => 'canceled',
                                              'updated_at' => Carbon::now()
                                          ]);

        Log::info('Existing subscriptions canceled', [
            'canceled_count' => $canceledCount,
            'tenant_id' => $payment->tenant_id
        ]);

        // Yeni abonelik verilerini hazırla
        $subscriptionData = [
            'tenant_id' => $payment->tenant_id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'payment_method' => 'kredi kartı',
            'subscription_data' => json_encode($billingData),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        Log::info('New subscription data prepared', $subscriptionData);

        // Yeni abonelik oluştur
        $subscription = TenantSubscription::create($subscriptionData);

        if (!$subscription) {
            throw new \Exception('Abonelik oluşturulamadı');
        }

        Log::info('New subscription created', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status
        ]);

        // Payment kaydını subscription ile ilişkilendir
        $paymentUpdated = $payment->update([
            'subscription_id' => $subscription->id,
            'updated_at' => Carbon::now()
        ]);

        Log::info('Payment record updated', [
            'success' => $paymentUpdated,
            'payment_id' => $payment->id,
            'subscription_id' => $subscription->id
        ]);

        ActivityLogger::log('subscription_purchased', "Yeni abonelik satın alındı: {$plan->name} ({$plan->billing_cycle})", [
            'module' => 'subscription',
            'reference_table' => 'tenant_subscriptions',
            'reference_id' => $subscription->id,
            'tenant_id' => $payment->tenant_id,
            'new_values' => [
                'plan_name' => $plan->name,
                'amount' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'status' => 'active',
                'starts_at' => $startDate->toDateTimeString(),
                'ends_at' => $endDate->toDateTimeString()
            ]
        ]);

        // Transaction'ı commit et
        DB::commit();
        
        Log::info('Transaction committed successfully', [
            'tenant_id' => $payment->tenant_id,
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id
        ]);

        // Son kontrol - veritabanından tekrar çek
        $finalTenant = Tenant::find($payment->tenant_id);
        $finalSubscription = TenantSubscription::find($subscription->id);

        Log::info('Final verification', [
            'tenant_status' => $finalTenant->subscription_status ?? 'NOT_SET',
            'tenant_ends_at' => $finalTenant->subscription_ends_at ?? 'NOT_SET',
            'subscription_status' => $finalSubscription->status ?? 'NOT_FOUND',
            'subscription_ends_at' => $finalSubscription->ends_at ?? 'NOT_FOUND'
        ]);

        return $subscription;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Subscription completion failed', [
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'payment_id' => $payment->id ?? 'unknown',
            'tenant_id' => $payment->tenant_id ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
        
        throw $e;
    }
    }

    /**
     * Ödeme durumu kontrol et (AJAX)
     */
    public function checkPaymentStatus(Request $request)
    {
        $paymentId = $request->input('payment_id');
        
        if (!$paymentId) {
            return response()->json(['status' => 'error', 'message' => 'Payment ID gerekli']);
        }

        $payment = SubscriptionPayment::find($paymentId);
        
        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Ödeme kaydı bulunamadı']);
        }

        return response()->json([
            'status' => $payment->status,
            'message' => $payment->status == 'completed' ? 'Ödeme başarılı' : 'Ödeme beklemede'
        ]);
    }

    
}