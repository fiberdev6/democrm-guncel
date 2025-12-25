<?php

namespace App\Contracts;

interface InvoiceIntegrationInterface
{
    /**
     * Müşteri oluştur veya güncelle
     */
    public function syncCustomer(array $customerData): array;
    
    /**
     * Fatura oluştur
     */
    public function createInvoice(array $invoiceData): array;
    
    /**
     * Fatura durumunu güncelle
     */
    public function updateInvoiceStatus(string $invoiceId, string $status): array;
    
    /**
     * Fatura PDF'ini indir
     */
    public function downloadInvoicePdf(string $invoiceId): string;
    
    /**
     * Bağlantıyı test et
     */
    public function testConnection(): bool;
}