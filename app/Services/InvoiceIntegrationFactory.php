<?php

namespace App\Services;

use App\Contracts\InvoiceIntegrationInterface;
use App\Models\IntegrationPurchase;
use App\Services\InvoiceIntegrations\ParasutService;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceIntegrationFactory
{
    /**
     * Firma için uygun entegrasyon service'ini döndür
     */
    public static function make(int $tenantId): ?InvoiceIntegrationInterface
    {
        $integration = IntegrationPurchase::where('tenant_id', $tenantId)
            ->whereHas('integration', function($q) {
                $q->where('category', 'invoice')
                  ->where('is_active', true);
            })
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            return null;
        }

        $credentials = $integration->credentials;
        
        if (is_string($credentials)) {
            $credentials = json_decode($credentials, true);
        }
        
        if (!is_array($credentials) || empty($credentials)) {
            throw new Exception('Geçersiz entegrasyon bilgileri');
        }
        
        $integrationSlug = $integration->integration->slug;

        return match($integrationSlug) {
            'parasut' => new ParasutService($credentials, $tenantId), // ← tenantId ekle
            default => throw new Exception('Bilinmeyen entegrasyon: ' . $integrationSlug)
        };
    }

    public static function hasIntegration(int $tenantId): bool
    {
        try {
            return self::make($tenantId) !== null;
        } catch (Exception $e) {
            Log::error('Entegrasyon kontrolü hatası: ' . $e->getMessage());
            return false;
        }
    }
}