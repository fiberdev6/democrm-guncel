<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\SubscriptionPayment;
use App\Services\PaymentGatewayService;
use App\Mail\SubscriptionActivatedMail;
use App\Mail\TrialEndingMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionService
{
    protected $paymentGateway;

    public function __construct(PaymentGatewayService $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Tenant için trial başlatma
     */
    public function startTrial(Tenant $tenant)
    {
        if ($tenant->trial_used) {
            throw new \Exception('Bu firma daha önce deneme süresi kullanmış.');
        }

        $tenant->startTrial();
        
        return $tenant;
    }

    /**
     * Abonelik satın alma
     */
    public function subscribe(Tenant $tenant, SubscriptionPlan $plan, array $paymentData)
    {
        // Ödeme işlemi
        $payment = $this->createPayment($tenant, $plan, $paymentData);
        
        try {
            $paymentResult = $this->paymentGateway->processPayment($payment);
            
            if ($paymentResult['success']) {
                // Ödeme başarılı - abonelik oluştur/güncelle
                $subscription = $this->activateSubscription($tenant, $plan, $paymentResult);
                
                // Ödeme kaydını güncelle
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $paymentResult['transaction_id'],
                    'paid_at' => now(),
                    'gateway_response' => $paymentResult['response']
                ]);

                // Email gönder
                Mail::to($tenant->email)->send(new SubscriptionActivatedMail($tenant, $subscription));

                return [
                    'success' => true,
                    'subscription' => $subscription,
                    'payment' => $payment
                ];
            } else {
                // Ödeme başarısız
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $paymentResult['message'],
                    'gateway_response' => $paymentResult['response'] ?? []
                ]);

                return [
                    'success' => false,
                    'message' => $paymentResult['message']
                ];
            }
        } catch (\Exception $e) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ödeme işlemi sırasında bir hata oluştu.'
            ];
        }
    }

    /**
     * Abonelik iptali
     */
    public function cancelSubscription(Tenant $tenant)
    {
        $subscription = $tenant->currentSubscription;
        
        if (!$subscription || !$subscription->isActive()) {
            throw new \Exception('Aktif abonelik bulunamadı.');
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now()
        ]);

        $tenant->update([
            'subscription_status' => 'canceled'
        ]);

        return $subscription;
    }

    /**
     * Süresi dolan abonelikleri güncelle
     */
    public function updateExpiredSubscriptions()
    {
        $expiredSubscriptions = TenantSubscription::where('status', '!=', 'expired')
                                                 ->where('ends_at', '<', now())
                                                 ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            
            $subscription->tenant->update([
                'subscription_status' => 'expired'
            ]);
        }

        return $expiredSubscriptions->count();
    }

    /**
     * Trial süresi biten firmaları bilgilendir
     */
    public function sendTrialReminders()
    {
        // 3 gün kala uyarı
        $trialEndingSoon = Tenant::onTrial()
                                ->whereBetween('trial_ends_at', [
                                    now()->addDays(3),
                                    now()->addDays(3)->endOfDay()
                                ])
                                ->get();

        foreach ($trialEndingSoon as $tenant) {
            Mail::to($tenant->email)->send(new TrialEndingMail($tenant));
        }

        return $trialEndingSoon->count();
    }

    /**
     * Ödeme kaydı oluştur
     */
    private function createPayment(Tenant $tenant, SubscriptionPlan $plan, array $paymentData)
    {
        return SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $tenant->currentSubscription->id ?? null,
            'payment_id' => 'PAY_' . Str::upper(Str::random(10)),
            'amount' => $plan->price,
            'currency' => 'TRY',
            'status' => 'pending',
            'payment_method' => $paymentData['payment_method'] ?? 'credit_card',
            'gateway' => $paymentData['gateway'] ?? config('services.payment.default_gateway')
        ]);
    }

    /**
     * Abonelik aktivasyonu
     */
    private function activateSubscription(Tenant $tenant, SubscriptionPlan $plan, array $paymentResult)
    {
        $endDate = $this->calculateEndDate($plan->billing_cycle);
        
        // Mevcut aboneliği güncelle veya yeni oluştur
        $subscription = $tenant->currentSubscription;
        
        if ($subscription) {
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'ends_at' => $endDate,
                'external_subscription_id' => $paymentResult['subscription_id'] ?? null
            ]);
        } else {
            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endDate,
                'external_subscription_id' => $paymentResult['subscription_id'] ?? null
            ]);
        }

        // Tenant durumunu güncelle
        $tenant->update([
            'subscription_status' => 'active',
            'subscription_ends_at' => $endDate
        ]);

        return $subscription;
    }

    /**
     * Bitiş tarihini hesapla
     */
    private function calculateEndDate($billingCycle)
    {
        return match($billingCycle) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
            default => now()->addMonth()
        };
    }
}