<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log($action, $description, $options = [])
    {
        try {
            $user = Auth::user();
            
            $logData = [
                'tenant_id' => $options['tenant_id'] ?? ($user ? $user->tenant_id : null),
                'user_id' => $user ? $user->user_id : null,
                'user_name' => $user ? $user->name : ($options['user_name'] ?? null),
                'user_role' => $user ? $user->getRoleNames()->first() : ($options['user_role'] ?? null),
                'ip_address' => Request::ip(),
                'action' => $action,
                'module' => $options['module'] ?? null,
                'description' => $description,
                'old_values' => isset($options['old_values']) ? json_encode($options['old_values']) : null,
                'new_values' => isset($options['new_values']) ? json_encode($options['new_values']) : null,
                'reference_table' => $options['reference_table'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'user_agent' => Request::header('User-Agent')
            ];

            ActivityLog::create($logData);
        } catch (\Exception $e) {
            // Log hatası sistem loglarını bozmasın diye sessizce geç
            \Log::error('ActivityLogger Error: ' . $e->getMessage());
        }
    }

    // Özel log metodları
    public static function logLogin($user)
    {
        self::log('login', 'Personel Giriş Yapıldı', [
            'module' => 'auth',
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first(),
            'tenant_id' => $user->tenant_id
        ]);
    }

    public static function logLogout($user)
    {
        self::log('logout', 'Personel Çıkış Yapıldı', [
            'module' => 'auth',
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first(),
            'tenant_id' => $user->tenant_id
        ]);
    }

   public static function logServiceCreated($serviceId)
{
    self::log('service_created', "ServisID: {$serviceId} Servis Kaydı Oluşturuldu", [
        'module' => 'service',
        'reference_table' => 'services',
        'reference_id' => $serviceId
    ]);
}

public static function logServiceUpdated($serviceId)
{
    self::log('service_updated', "ServisID: {$serviceId} Servis Bilgileri Güncellendi", [
        'module' => 'service',
        'reference_table' => 'services',
        'reference_id' => $serviceId
    ]);
}

public static function logServiceDeleted($serviceId)
{
    self::log('service_deleted', "ServisID: {$serviceId} Servis Kaydı Silindi", [
        'module' => 'service',
        'reference_table' => 'services',
        'reference_id' => $serviceId
    ]);
}

    // public static function logServiceStatusChanged($serviceId, $oldStatus, $newStatus)
    // {
    //     // Durum ID'lerini metne çevir
    //     $statusMap = [
    //         235 => 'Yeni Servisler',
    //         236 => 'Teknisyen Yönledir',
    //         237 => 'Cihaz Atölyeye Alındı',
    //         244 => 'Müşteri İptal Etti',
    //         252 => 'Teslimata Hazır(Tamamlandı)',
    //         // Diğer durumları ekleyebilirsiniz
    //     ];

    //     $oldStatusText = $statusMap[$oldStatus] ?? "Durum {$oldStatus}";
    //     $newStatusText = $statusMap[$newStatus] ?? "Durum {$newStatus}";

    //     self::log('service_status_changed', "ServisID: {$serviceId} Servis Durumu Değiştirildi: {$oldStatusText} -> {$newStatusText}", [
    //         'module' => 'service',
    //         'reference_table' => 'services',
    //         'reference_id' => $serviceId,
    //         'old_values' => ['status' => $oldStatus],
    //         'new_values' => ['status' => $newStatus]
    //     ]);
    // }


    //Stok log metodları
    public static function logStockCreated($stockId, $productName = null)
    {
        $description = $productName 
            ? "StokID: {$stockId} Stok Oluşturuldu: {$productName}"
            : "StokID: {$stockId} Stok Oluşturuldu";
            
        self::log('stock_created', $description, [
            'module' => 'stock',
            'reference_table' => 'stocks',
            'reference_id' => $stockId
        ]);
    }

    public static function logConsignmentCreated($stockId, $productName = null)
    {
        $description = $productName 
            ? "StokID: {$stockId} Konsinye Cihaz Oluşturuldu: {$productName}"
            : "StokID: {$stockId} Konsinye Cihaz Oluşturuldu";
            
        self::log('consignment_created', $description, [
            'module' => 'stock',
            'reference_table' => 'stocks',
            'reference_id' => $stockId
        ]);
    }

    public static function logStockAction($stockId, $action, $quantity, $actionId = null)
    {
        $actionTexts = [
            1 => 'Stok Girişi',
            2 => 'Serviste Kullanım',
            3 => 'Personele Gönderim',
            4 => 'Müşteriden Geri Alma'
        ];

        $actionText = $actionTexts[$action] ?? "İşlem {$action}";
        
        self::log('stock_action', "StokID: {$stockId} {$actionText}: {$quantity} Adet", [
            'module' => 'stock',
            'reference_table' => 'stock_actions',
            'reference_id' => $actionId
        ]);
    }

 //Kas Hareketleri log metodları
public static function logCashTransaction($amount, $type, $description)
    {
        $typeText = $type == 1 ? 'Gelir' : 'Gider';
        self::log('cash_transaction', "Kasa İşlemi: {$typeText} - {$amount} TL - {$description}", [
            'module' => 'cash'
        ]);
    }

    public static function logCashTransactionUpdated($transactionId, $amount, $type, $description)
    {
        $typeText = $type == 1 ? 'Gelir' : 'Gider';
        self::log('cash_transaction_updated', "KasaID: {$transactionId} Kasa İşlemi Güncellendi: {$typeText} - {$amount} TL", [
            'module' => 'cash',
            'reference_table' => 'cash_transactions',
            'reference_id' => $transactionId
        ]);
    }

    public static function logCashTransactionDeleted($transactionId, $amount, $type, $description)
    {
        $typeText = $type == 1 ? 'Gelir' : 'Gider';
        self::log('cash_transaction_deleted', "KasaID: {$transactionId} Kasa İşlemi Silindi: {$typeText} - {$amount} TL", [
            'module' => 'cash',
            'reference_table' => 'cash_transactions',
            'reference_id' => $transactionId
        ]);
    }
public static function logServicePlanDeleted($serviceId, $stageName, $planId = null)
{
    self::log('service_plan_deleted', "ServisID: {$serviceId} Aşama Silindi: {$stageName}", [
        'module' => 'service',
        'reference_table' => 'service_plannings',
        'reference_id' => $planId
    ]);
}
public static function logServiceMoneyAdded($serviceId, $amount, $type, $description)
{
    $typeText = $type == 1 ? 'Gelir' : 'Gider';
    self::log('service_money_added', "ServisID: {$serviceId} Para İşlemi: {$typeText} - {$amount} TL", [
        'module' => 'service_finance',
        'reference_table' => 'service_money_actions',
        'reference_id' => $serviceId
    ]);
}

public static function logServicePhotoAdded($serviceId, $photoId = null)
{
    self::log('service_photo_added', "ServisID: {$serviceId} Fotoğraf Eklendi", [
        'module' => 'service',
        'reference_table' => 'service_photos',
        'reference_id' => $photoId
    ]);
}

public static function logServicePhotoDeleted($serviceId, $photoId = null)
{
    self::log('service_photo_deleted', "ServisID: {$serviceId} Fotoğraf Silindi", [
        'module' => 'service',
        'reference_table' => 'service_photos',
        'reference_id' => $photoId
    ]);
}

public static function logServiceNoteAdded($serviceId, $noteType, $noteId = null)
{
    $noteTypeText = $noteType == 'receipt' ? 'Fiş Notu' : 'Operatör Notu';
    self::log('service_note_added', "ServisID: {$serviceId} {$noteTypeText} Eklendi", [
        'module' => 'service',
        'reference_table' => $noteType == 'receipt' ? 'service_receipt_notes' : 'service_opt_notes',
        'reference_id' => $noteId
    ]);
}
public static function logServicePlanAdded($serviceId, $planId, $stageName)
{
        self::log('service_plan_added', "ServisID: {$serviceId} Aşama Eklendi: {$stageName}", [
            'module' => 'service',
            'reference_table' => 'service_plannings',
            'reference_id' => $planId
        ]);
}

// Müşteri Log Metodları
    public static function logCustomerCreated($customerId, $customerName = null)
    {
        $description = $customerName 
            ? "MusteriID: {$customerId} Müşteri Kaydı Oluşturuldu: {$customerName}"
            : "MusteriID: {$customerId} Müşteri Kaydı Oluşturuldu";
            
        self::log('customer_created', $description, [
            'module' => 'customer',
            'reference_table' => 'customers',
            'reference_id' => $customerId
        ]);
    }

    public static function logCustomerUpdated($customerId, $customerName = null)
    {
        $description = $customerName 
            ? "MusteriID: {$customerId} Müşteri Bilgileri Güncellendi: {$customerName}"
            : "MusteriID: {$customerId} Müşteri Bilgileri Güncellendi";
            
        self::log('customer_updated', $description, [
            'module' => 'customer',
            'reference_table' => 'customers',
            'reference_id' => $customerId
        ]);
    }

    public static function logCustomerDeleted($customerId, $customerName = null)
    {
        $description = $customerName 
            ? "MusteriID: {$customerId} Müşteri Kaydı Silindi: {$customerName}"
            : "MusteriID: {$customerId} Müşteri Kaydı Silindi";
            
        self::log('customer_deleted', $description, [
            'module' => 'customer',
            'reference_table' => 'customers',
            'reference_id' => $customerId
        ]);
    }
 // Personel Log Metodları
    public static function logStaffCreated($staffId, $staffName = null, $role = null)
    {
        $description = $staffName 
            ? "PersonelID: {$staffId} Personel Kaydı Oluşturuldu: {$staffName}" . ($role ? " ({$role})" : "")
            : "PersonelID: {$staffId} Personel Kaydı Oluşturuldu";
            
        self::log('staff_created', $description, [
            'module' => 'staff',
            'reference_table' => 'users',
            'reference_id' => $staffId
        ]);
    }

    public static function logStaffUpdated($staffId, $staffName = null, $role = null)
    {
        $description = $staffName 
            ? "PersonelID: {$staffId} Personel Bilgileri Güncellendi: {$staffName}" . ($role ? " ({$role})" : "")
            : "PersonelID: {$staffId} Personel Bilgileri Güncellendi";
            
        self::log('staff_updated', $description, [
            'module' => 'staff',
            'reference_table' => 'users',
            'reference_id' => $staffId
        ]);
    }

    public static function logStaffDeleted($staffId, $staffName = null, $role = null)
    {
        $description = $staffName 
            ? "PersonelID: {$staffId} Personel Kaydı Silindi: {$staffName}" . ($role ? " ({$role})" : "")
            : "PersonelID: {$staffId} Personel Kaydı Silindi";
            
        self::log('staff_deleted', $description, [
            'module' => 'staff',
            'reference_table' => 'users',
            'reference_id' => $staffId
        ]);
    }

    // Bayi Log Metodları
    public static function logDealerCreated($dealerId, $dealerName = null)
    {
        $description = $dealerName 
            ? "BayiID: {$dealerId} Bayi Kaydı Oluşturuldu: {$dealerName}"
            : "BayiID: {$dealerId} Bayi Kaydı Oluşturuldu";
            
        self::log('dealer_created', $description, [
            'module' => 'dealer',
            'reference_table' => 'users',
            'reference_id' => $dealerId
        ]);
    }

    public static function logDealerUpdated($dealerId, $dealerName = null)
    {
        $description = $dealerName 
            ? "BayiID: {$dealerId} Bayi Bilgileri Güncellendi: {$dealerName}"
            : "BayiID: {$dealerId} Bayi Bilgileri Güncellendi";
            
        self::log('dealer_updated', $description, [
            'module' => 'dealer',
            'reference_table' => 'users',
            'reference_id' => $dealerId
        ]);
    }

    public static function logDealerDeleted($dealerId, $dealerName = null)
    {
        $description = $dealerName 
            ? "BayiID: {$dealerId} Bayi Kaydı Silindi: {$dealerName}"
            : "BayiID: {$dealerId} Bayi Kaydı Silindi";
            
        self::log('dealer_deleted', $description, [
            'module' => 'dealer',
            'reference_table' => 'users',
            'reference_id' => $dealerId
        ]);
    }
    // Fatura Log Metodları
    public static function logInvoiceCreated($invoiceId, $invoiceNumber = null, $customerName = null)
    {
        $description = "FaturaID: {$invoiceId} Fatura Oluşturuldu";
        if ($invoiceNumber) {
            $description .= " - Fatura No: {$invoiceNumber}";
        }
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('invoice_created', $description, [
            'module' => 'invoice',
            'reference_table' => 'invoices',
            'reference_id' => $invoiceId
        ]);
    }

    public static function logInvoiceUpdated($invoiceId, $invoiceNumber = null, $customerName = null)
    {
        $description = "FaturaID: {$invoiceId} Fatura Güncellendi";
        if ($invoiceNumber) {
            $description .= " - Fatura No: {$invoiceNumber}";
        }
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('invoice_updated', $description, [
            'module' => 'invoice',
            'reference_table' => 'invoices',
            'reference_id' => $invoiceId
        ]);
    }

    public static function logInvoiceDeleted($invoiceId, $invoiceNumber = null, $customerName = null)
    {
        $description = "FaturaID: {$invoiceId} Fatura Silindi";
        if ($invoiceNumber) {
            $description .= " - Fatura No: {$invoiceNumber}";
        }
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('invoice_deleted', $description, [
            'module' => 'invoice',
            'reference_table' => 'invoices',
            'reference_id' => $invoiceId
        ]);
    }
    // Teklif Log Metodları
    public static function logOfferCreated($offerId, $customerName = null)
    {
        $description = "TeklifID: {$offerId} Teklif Oluşturuldu";
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('offer_created', $description, [
            'module' => 'offer',
            'reference_table' => 'offers',
            'reference_id' => $offerId
        ]);
    }

    public static function logOfferUpdated($offerId, $customerName = null)
    {
        $description = "TeklifID: {$offerId} Teklif Güncellendi";
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('offer_updated', $description, [
            'module' => 'offer',
            'reference_table' => 'offers',
            'reference_id' => $offerId
        ]);
    }

    public static function logOfferDeleted($offerId, $customerName = null)
    {
        $description = "TeklifID: {$offerId} Teklif Silindi";
        if ($customerName) {
            $description .= " - Müşteri: {$customerName}";
        }
            
        self::log('offer_deleted', $description, [
            'module' => 'offer',
            'reference_table' => 'offers',
            'reference_id' => $offerId
        ]);
    }

   // Destek Talepleri Log Metodları
    public static function logSupportTicketCreated($ticketId, $ticketNumber, $subject = null)
    {
        $description = "TalepID: {$ticketId} Destek Talebi Oluşturuldu - #{$ticketNumber}";
        if ($subject) {
            $description .= " - {$subject}";
        }
            
        self::log('support_ticket_created', $description, [
            'module' => 'support',
            'reference_table' => 'support_tickets',
            'reference_id' => $ticketId
        ]);
    }

    public static function logSupportTicketUpdated($ticketId, $ticketNumber, $subject = null)
    {
        $description = "TalepID: {$ticketId} Destek Talebi Güncellendi - #{$ticketNumber}";
        if ($subject) {
            $description .= " - {$subject}";
        }
            
        self::log('support_ticket_updated', $description, [
            'module' => 'support',
            'reference_table' => 'support_tickets',
            'reference_id' => $ticketId
        ]);
    }

    public static function logSupportTicketClosed($ticketId, $ticketNumber, $subject = null)
    {
        $description = "TalepID: {$ticketId} Destek Talebi Kapatıldı - #{$ticketNumber}";
        if ($subject) {
            $description .= " - {$subject}";
        }
            
        self::log('support_ticket_closed', $description, [
            'module' => 'support',
            'reference_table' => 'support_tickets',
            'reference_id' => $ticketId
        ]);
    }

    public static function logSupportTicketReopened($ticketId, $ticketNumber, $subject = null)
    {
        $description = "TalepID: {$ticketId} Destek Talebi Yeniden Açıldı - #{$ticketNumber}";
        if ($subject) {
            $description .= " - {$subject}";
        }
            
        self::log('support_ticket_reopened', $description, [
            'module' => 'support',
            'reference_table' => 'support_tickets',
            'reference_id' => $ticketId
        ]);
    }

    public static function logSupportTicketReply($ticketId, $ticketNumber, $isAdminReply = false)
    {
        $replyType = $isAdminReply ? 'Admin Yanıtı' : 'Kullanıcı Yanıtı';
        $description = "TalepID: {$ticketId} Destek Talebine {$replyType} Eklendi - #{$ticketNumber}";
            
        self::log('support_ticket_reply', $description, [
            'module' => 'support',
            'reference_table' => 'support_ticket_replies',
            'reference_id' => $ticketId
        ]);
    }

    public static function logSupportTicketStatusChanged($ticketId, $ticketNumber, $oldStatus, $newStatus)
    {
        $statusMap = [
            'acik' => 'Açık',
            'cevaplandi' => 'Cevaplandı',
            'kapali' => 'Kapalı'
        ];

        $oldStatusText = $statusMap[$oldStatus] ?? $oldStatus;
        $newStatusText = $statusMap[$newStatus] ?? $newStatus;

        self::log('support_ticket_status_changed', "TalepID: {$ticketId} Destek Talebi Durumu Değiştirildi - #{$ticketNumber}: {$oldStatusText} -> {$newStatusText}", [
            'module' => 'support',
            'reference_table' => 'support_tickets',
            'reference_id' => $ticketId,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus]
        ]);
    }


    /**
     * Abonelik plan satın alma log kaydı
     */
    public static function logSubscriptionPurchased($subscriptionId, $planName, $amount, $billingCycle)
    {
        self::log('subscription_purchased', "Yeni abonelik satın alındı: {$planName} ({$billingCycle})", [
            'module' => 'subscription',
            'reference_table' => 'tenant_subscriptions',
            'reference_id' => $subscriptionId,
            'new_values' => [
                'plan_name' => $planName,
                'amount' => $amount,
                'billing_cycle' => $billingCycle,
                'status' => 'active'
            ]
        ]);
    }

    /**
     * Abonelik plan yükseltme log kaydı
     */
    public static function logSubscriptionUpgraded($subscriptionId, $oldPlanName, $newPlanName, $oldAmount, $newAmount)
    {
        self::log('subscription_upgraded', "Abonelik yükseltildi: {$oldPlanName} → {$newPlanName}", [
            'module' => 'subscription',
            'reference_table' => 'tenant_subscriptions',
            'reference_id' => $subscriptionId,
            'old_values' => [
                'plan_name' => $oldPlanName,
                'amount' => $oldAmount
            ],
            'new_values' => [
                'plan_name' => $newPlanName,
                'amount' => $newAmount
            ]
        ]);
    }

    /**
     * Abonelik iptal log kaydı
     */
    public static function logSubscriptionCanceled($subscriptionId, $planName, $reason = null)
    {
        self::log('subscription_canceled', "Abonelik iptal edildi: {$planName}" . ($reason ? " - Sebep: {$reason}" : ""), [
            'module' => 'subscription',
            'reference_table' => 'tenant_subscriptions',
            'reference_id' => $subscriptionId,
            'old_values' => [
                'plan_name' => $planName,
                'status' => 'active'
            ],
            'new_values' => [
                'status' => 'canceled',
                'cancellation_reason' => $reason
            ]
        ]);
    }


    /**
     * Ödeme başarılı log kaydı
     */
    public static function logPaymentSuccess($paymentId, $amount, $paymentMethod, $orderId)
    {
        self::log('payment_success', "Ödeme başarıyla tamamlandı: {$amount} TL ({$paymentMethod})", [
            'module' => 'payment',
            'reference_table' => 'subscription_payments',
            'reference_id' => $paymentId,
            'new_values' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'completed'
            ]
        ]);
    }

    /**
     * Ödeme başarısız log kaydı
     */
    public static function logPaymentFailed($paymentId, $amount, $paymentMethod, $failureReason)
    {
        self::log('payment_failed', "Ödeme başarısız oldu: {$amount} TL - {$failureReason}", [
            'module' => 'payment',
            'reference_table' => 'subscription_payments',
            'reference_id' => $paymentId,
            'new_values' => [
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'failed',
                'failure_reason' => $failureReason
            ]
        ]);
    }

/**
 * Deneme süresi başlama log kaydı
 */
public static function logTrialStarted($tenantId, $trialDays)
{
    self::log('trial_started', "Deneme süresi başladı: {$trialDays} gün", [
        'module' => 'subscription',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'new_values' => [
            'trial_days' => $trialDays,
            'trial_started_at' => now()->toDateTimeString()
        ]
    ]);
}

/**
 * Abonelik yenileme log kaydı
 */
public static function logSubscriptionRenewed($subscriptionId, $planName, $amount, $newEndDate)
{
    self::log('subscription_renewed', "Abonelik yenilendi: {$planName}", [
        'module' => 'subscription',
        'reference_table' => 'tenant_subscriptions',
        'reference_id' => $subscriptionId,
        'new_values' => [
            'plan_name' => $planName,
            'amount' => $amount,
            'renewed_at' => now()->toDateTimeString(),
            'new_end_date' => $newEndDate
        ]
    ]);
}

/**
 * Storage satın alma log kaydı
 */
public static function logStoragePurchased($purchaseId, $storageGb, $amount, $packageName)
{
    self::log('storage_purchased', "Ek depolama satın alındı: +{$storageGb} GB ({$packageName})", [
        'module' => 'storage',
        'reference_table' => 'storage_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'storage_gb' => $storageGb,
            'amount' => $amount,
            'package_name' => $packageName,
            'status' => 'completed'
        ]
    ]);
}

/**
 * Storage ödeme başarısız log kaydı
 */
public static function logStoragePurchaseFailed($purchaseId, $storageGb, $amount, $failureReason)
{
    self::log('storage_purchase_failed', "Depolama satın alma başarısız: {$storageGb} GB - {$failureReason}", [
        'module' => 'storage',
        'reference_table' => 'storage_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'storage_gb' => $storageGb,
            'amount' => $amount,
            'status' => 'failed',
            'failure_reason' => $failureReason
        ]
    ]);
}

/**
 * Entegrasyon satın alma log kaydı
 */
public static function logIntegrationPurchased($purchaseId, $integrationName, $amount)
{
    self::log('integration_purchased', "Entegrasyon satın alındı: {$integrationName}", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'integration_name' => $integrationName,
            'amount' => $amount,
            'status' => 'completed',
            'activated_at' => now()->toDateTimeString()
        ]
    ]);
}

/**
 * Entegrasyon ödeme başarısız log kaydı
 */
public static function logIntegrationPurchaseFailed($purchaseId, $integrationName, $amount, $failureReason)
{
    self::log('integration_purchase_failed', "Entegrasyon satın alma başarısız: {$integrationName} - {$failureReason}", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'integration_name' => $integrationName,
            'amount' => $amount,
            'status' => 'failed',
            'failure_reason' => $failureReason
        ]
    ]);
}

/**
 * Hipcall webhook oluşturma log kaydı
 */
public static function logHipcallWebhookCreated($purchaseId, $webhookUrl)
{
    self::log('hipcall_webhook_created', "Hipcall webhook oluşturuldu", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'webhook_url' => $webhookUrl,
            'created_at' => now()->toDateTimeString()
        ]
    ]);
}

/**
 * Storage satın alma başlatma log kaydı
 */
public static function logStoragePurchaseInitiated($purchaseId, $packageName, $storageGb, $amount, $paymentToken)
{
    self::log('storage_purchase_initiated', "Depolama satın alma başlatıldı: {$packageName} ({$storageGb} GB)", [
        'module' => 'storage',
        'reference_table' => 'storage_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'package_name' => $packageName,
            'storage_gb' => $storageGb,
            'amount' => $amount,
            'status' => 'pending',
            'payment_token' => $paymentToken
        ]
    ]);
}

/**
 * Storage ödeme iframe oluşturma hatası log kaydı
 */
public static function logStoragePaymentIframeError($purchaseId, $errorMessage)
{
    self::log('storage_payment_iframe_error', "Depolama ödeme sayfası oluşturulamadı: {$errorMessage}", [
        'module' => 'storage',
        'reference_table' => 'storage_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'error' => $errorMessage,
            'status' => 'failed'
        ]
    ]);
}

/**
 * Storage ödeme başarı sayfası log kaydı
 */
public static function logStoragePaymentSuccessPage($tenantId, $message, $packageName = null)
{
    self::log('storage_payment_success_page', "Depolama ödeme başarı sayfasına yönlendirildi", [
        'module' => 'storage',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'new_values' => [
            'message' => $message,
            'package_name' => $packageName
        ]
    ]);
}

/**
 * Storage ödeme hata sayfası log kaydı
 */
public static function logStoragePaymentFailPage($tenantId, $message, $reason = null)
{
    self::log('storage_payment_fail_page', "Depolama ödeme başarısız - Kullanıcı hata sayfasına yönlendirildi" . ($reason ? ": {$reason}" : ""), [
        'module' => 'storage',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'new_values' => [
            'message' => $message,
            'reason' => $reason
        ]
    ]);
}
/**
 * Entegrasyon satın alma başlatma log kaydı
 */
public static function logIntegrationPurchaseInitiated($purchaseId, $integrationName, $amount, $paymentToken)
{
    self::log('integration_purchase_initiated', "Entegrasyon satın alma başlatıldı: {$integrationName}", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'integration_name' => $integrationName,
            'amount' => $amount,
            'status' => 'pending',
            'payment_token' => $paymentToken
        ]
    ]);
}

/**
 * Ücretsiz entegrasyon aktifleştirme log kaydı
 */
public static function logIntegrationFreeActivated($purchaseId, $integrationName)
{
    self::log('integration_free_activated', "Ücretsiz entegrasyon aktifleştirildi: {$integrationName}", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'integration_name' => $integrationName,
            'status' => 'completed',
            'is_active' => true,
            'activated_at' => now()->toDateTimeString()
        ]
    ]);
}

/**
 * Entegrasyon ödeme iframe hatası log kaydı
 */
public static function logIntegrationPaymentIframeError($purchaseId, $errorMessage)
{
    self::log('integration_payment_iframe_error', "Entegrasyon ödeme sayfası oluşturulamadı: {$errorMessage}", [
        'module' => 'integration',
        'reference_table' => 'integration_purchases',
        'reference_id' => $purchaseId,
        'new_values' => [
            'error' => $errorMessage,
            'status' => 'failed'
        ]
    ]);
}

/**
 * Entegrasyon ödeme başarı sayfası log kaydı
 */
public static function logIntegrationPaymentSuccessPage($tenantId, $message, $integrationName = null)
{
    self::log('integration_payment_success_page', "Entegrasyon ödeme başarı sayfasına yönlendirildi", [
        'module' => 'integration',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'new_values' => [
            'message' => $message,
            'integration_name' => $integrationName
        ]
    ]);
}

/**
 * Entegrasyon ödeme hata sayfası log kaydı
 */
public static function logIntegrationPaymentFailPage($tenantId, $message, $reason = null)
{
    self::log('integration_payment_fail_page', "Entegrasyon ödeme başarısız - Kullanıcı hata sayfasına yönlendirildi" . ($reason ? ": {$reason}" : ""), [
        'module' => 'integration',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'new_values' => [
            'message' => $message,
            'reason' => $reason
        ]
    ]);
}

/**
 * Abonelik ödeme başarı sayfası log kaydı
 */
public static function logSubscriptionPaymentSuccessPage($paymentId, $tenantId, $amount, $subscriptionId = null)
{
    self::log('subscription_payment_success_page', "Abonelik ödeme başarı sayfasına yönlendirildi", [
        'module' => 'subscription',
        'reference_table' => 'subscription_payments',
        'reference_id' => $paymentId,
        'tenant_id' => $tenantId,
        'new_values' => [
            'message' => 'Ödemeniz başarıyla tamamlandı! Aboneliğiniz aktif edildi.',
            'amount' => $amount,
            'payment_id' => $paymentId,
            'subscription_id' => $subscriptionId
        ]
    ]);
}

/**
 * Abonelik ödeme başarısız sayfası log kaydı
 */
public static function logSubscriptionPaymentFailPage($paymentId, $tenantId, $errorMessage, $failureReason = null)
{
    self::log('subscription_payment_fail_page', "Abonelik ödeme başarısız - Kullanıcı hata sayfasına yönlendirildi: " . ($failureReason ?? 'Bilinmeyen hata'), [
        'module' => 'subscription',
        'reference_table' => 'subscription_payments',
        'reference_id' => $paymentId,
        'tenant_id' => $tenantId,
        'new_values' => [
            'message' => $errorMessage,
            'failure_reason' => $failureReason
        ]
    ]);
}

/**
 * Ödeme durumu bekliyor log kaydı
 */
public static function logSubscriptionPaymentPending($paymentId, $tenantId, $status)
{
    self::log('subscription_payment_pending', "Abonelik ödemesi henüz tamamlanmadı", [
        'module' => 'subscription',
        'reference_table' => 'subscription_payments',
        'reference_id' => $paymentId,
        'tenant_id' => $tenantId,
        'new_values' => [
            'status' => $status,
            'payment_id' => $paymentId
        ]
    ]);
}

/**
 * Ödeme kaydı bulunamadı log kaydı
 */
public static function logSubscriptionPaymentNotFound($paymentId, $tenantId, $planId = null)
{
    self::log('subscription_payment_not_found', "Abonelik ödeme kaydı bulunamadı", [
        'module' => 'subscription',
        'reference_table' => 'tenants',
        'reference_id' => $tenantId,
        'tenant_id' => $tenantId,
        'new_values' => [
            'payment_id' => $paymentId,
            'plan_id' => $planId
        ]
    ]);
}
/**
 * Toplu SMS gönderim başarılı log kaydı
 */
public static function logBulkSmsSuccess($tenantId, $recipientCount, $provider, $message)
{
    self::log('bulk_sms_sent', "Toplu SMS başarıyla gönderildi: {$recipientCount} alıcı", [
        'module' => 'bulk_sms',
        'reference_table' => 'bulk_sms',
        'reference_id' => null,
        'tenant_id' => $tenantId,
        'new_values' => [
            'recipient_count' => $recipientCount,
            'provider' => $provider,
            'message_preview' => substr($message, 0, 50) . '...',
            'status' => 'success'
        ]
    ]);
}

/**
 * Toplu SMS gönderim başarısız log kaydı
 */
public static function logBulkSmsFailed($tenantId, $recipientCount, $provider, $errorMessage)
{
    self::log('bulk_sms_failed', "Toplu SMS gönderimi başarısız: {$errorMessage}", [
        'module' => 'bulk_sms',
        'reference_table' => 'bulk_sms',
        'reference_id' => null,
        'tenant_id' => $tenantId,
        'new_values' => [
            'recipient_count' => $recipientCount,
            'provider' => $provider,
            'error_message' => $errorMessage,
            'status' => 'failed'
        ]
    ]);
}

/**
 * Toplu SMS filtre uygulandı log kaydı
 */
public static function logBulkSmsFilterApplied($tenantId, $filterData, $resultCount)
{
    self::log('bulk_sms_filter_applied', "Toplu SMS filtresi uygulandı: {$resultCount} sonuç bulundu", [
        'module' => 'bulk_sms',
        'reference_table' => null,
        'reference_id' => null,
        'tenant_id' => $tenantId,
        'new_values' => [
            'filters' => $filterData,
            'result_count' => $resultCount
        ]
    ]);
}

/**
 * Toplu SMS geçersiz telefon log kaydı
 */
public static function logBulkSmsInvalidPhones($tenantId, $invalidCount, $totalCount)
{
    self::log('bulk_sms_invalid_phones', "Toplu SMS - Geçersiz telefon numaraları atlandı: {$invalidCount}/{$totalCount}", [
        'module' => 'bulk_sms',
        'reference_table' => null,
        'reference_id' => null,
        'tenant_id' => $tenantId,
        'new_values' => [
            'invalid_count' => $invalidCount,
            'total_count' => $totalCount
        ]
    ]);
}

/**
 * SMS provider yapılandırma hatası log kaydı
 */
public static function logSmsProviderConfigError($tenantId, $providerId, $errorMessage)
{
    self::log('sms_provider_config_error', "SMS provider yapılandırma hatası: {$errorMessage}", [
        'module' => 'bulk_sms',
        'reference_table' => 'integration_purchases',
        'reference_id' => $providerId,
        'tenant_id' => $tenantId,
        'new_values' => [
            'error' => $errorMessage,
            'provider_id' => $providerId
        ]
    ]);
}




}