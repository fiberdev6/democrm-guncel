<?php

namespace App\Services;

use App\Models\SubscriptionPayment;

class PaymentGatewayService
{
    public function processPayment(SubscriptionPayment $payment)
    {
        // Bu method'u kullandığınız ödeme gateway'ine göre implement edin
        // Örnek: Stripe, İyzico, PayPal
        
        switch ($payment->gateway) {
            case 'stripe':
                return $this->processStripePayment($payment);
            case 'iyzico':
                return $this->processIyzicoPayment($payment);
            default:
                return $this->mockPaymentProcess($payment);
        }
    }

    private function processStripePayment(SubscriptionPayment $payment)
    {
        // Stripe entegrasyonu buraya gelecek
        // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        
        // Örnek implementation...
        return [
            'success' => true,
            'transaction_id' => 'stripe_' . uniqid(),
            'subscription_id' => 'sub_' . uniqid(),
            'message' => 'Ödeme başarılı',
            'response' => ['gateway' => 'stripe']
        ];
    }

    private function processIyzicoPayment(SubscriptionPayment $payment)
    {
        // İyzico entegrasyonu buraya gelecek
        return [
            'success' => true,
            'transaction_id' => 'iyzico_' . uniqid(),
            'message' => 'Ödeme başarılı',
            'response' => ['gateway' => 'iyzico']
        ];
    }

    private function mockPaymentProcess(SubscriptionPayment $payment)
    {
        // Test/Demo amaçlı mock payment
        $success = rand(1, 10) > 2; // %80 başarı oranı
        
        return [
            'success' => $success,
            'transaction_id' => $success ? 'mock_' . uniqid() : null,
            'message' => $success ? 'Ödeme başarılı' : 'Kart reddedildi',
            'response' => ['gateway' => 'mock', 'test' => true]
        ];
    }
}