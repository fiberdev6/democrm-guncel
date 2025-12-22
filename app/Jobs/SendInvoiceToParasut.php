<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceIntegrationFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceToParasut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoice;
    public $invoiceData;
    public $invoiceProducts;

    public $tries = 3; // 3 kez dene
    public $timeout = 120; // 2 dakika timeout

    public function __construct(Invoice $invoice, array $invoiceData, array $invoiceProducts)
    {
        $this->invoice = $invoice;
        $this->invoiceData = $invoiceData;
        $this->invoiceProducts = $invoiceProducts;
    }

    public function handle()
    {
        try {
            Log::info('Job başlatıldı - Fatura Paraşüt\'e gönderiliyor', [
                'invoice_id' => $this->invoice->id
            ]);

            $integration = InvoiceIntegrationFactory::make($this->invoice->firma_id);
            
            if (!$integration) {
                Log::warning('Entegrasyon bulunamadı', ['invoice_id' => $this->invoice->id]);
                return;
            }

            // Müşteri bilgilerini al
            $customer = Customer::with(['country', 'state'])->find($this->invoice->musteriid);
            
            if (!$customer) {
                throw new \Exception('Müşteri bulunamadı');
            }

            // Müşteri verilerini hazırla
            $customerData = [
                'adSoyad' => $customer->adSoyad,
                'musteriTipi' => $customer->musteriTipi,
                'email' => $customer->email ?? null,
                'tel1' => $customer->tel1 ?? null,
                'vergiNo' => $customer->vergiNo ?? null,
                'vergiDairesi' => $customer->vergiDairesi ?? null,
                'tcNo' => $customer->tcNo ?? null,
                'adres' => $customer->adres ?? null,
                'il' => $customer->country->name ?? null,
                'ilce' => $customer->state->ilceName ?? null,
            ];

            // Paraşüt'e gönder
            $result = $integration->createInvoice(array_merge($this->invoiceData, [
                'customer' => $customerData,
                'items' => $this->invoiceProducts
            ]));

            if ($result['success']) {
                $this->invoice->update([
                    'faturaDurumu' => 'draft',
                    'integration_invoice_id' => $result['invoice_id'],
                    'integration_error' => null,
                    'faturaNumarasi' => $result['invoice_number'] ?? $this->invoice->faturaNumarasi,
                ]);
                
                Log::info('Fatura başarıyla Paraşüt\'e gönderildi', [
                    'invoice_id' => $this->invoice->id,
                    'parasut_id' => $result['invoice_id']
                ]);
            } else {
                $this->invoice->update([
                    'faturaDurumu' => 'error',
                    'integration_error' => $result['error'] ?? 'Bilinmeyen hata'
                ]);
                
                Log::error('Fatura Paraşüt\'e gönderilemedi', [
                    'invoice_id' => $this->invoice->id,
                    'error' => $result['error'] ?? 'Bilinmeyen hata'
                ]);
            }

        } catch (\Exception $e) {
            $this->invoice->update([
                'faturaDurumu' => 'error',
                'integration_error' => $e->getMessage()
            ]);
            
            Log::error('Job hatası: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Retry mekanizması için
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job tamamen başarısız oldu', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage()
        ]);

        $this->invoice->update([
            'faturaDurumu' => 'error',
            'integration_error' => 'Maksimum deneme sayısı aşıldı: ' . $exception->getMessage()
        ]);
    }
}
