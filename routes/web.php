<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\HomeSliderController;
use App\Http\Controllers\Backend\SiteSettingsController;
use App\Http\Controllers\Backend\CompanySettingsController;
use App\Http\Controllers\Backend\SocialMediaSettingController;
use App\Http\Controllers\Backend\HomeCardController;
use App\Http\Controllers\Backend\MenusController;
use App\Http\Controllers\Backend\ClientsController;
use App\Http\Controllers\Backend\ContactController;
use App\Http\Controllers\Backend\PagesController;
use App\Http\Controllers\Backend\EmailSetingsController;
use App\Http\Controllers\Backend\GoogleSettingController;
use App\Http\Controllers\Backend\HomeKayitController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\AboutController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\ReferencesController;
use App\Http\Controllers\Backend\RoomImageController;
use App\Http\Controllers\Backend\RoomFacilityController;
use App\Http\Controllers\Backend\DocumentsController;
use App\Http\Controllers\Backend\MisyonController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductImageController;
use App\Http\Controllers\Backend\PrivacyPolicyController;
use App\Http\Controllers\Backend\CategoriesController;
use App\Http\Controllers\Backend\CategoryImagesController;
use App\Http\Controllers\Backend\FeatureImagesController;
use App\Http\Controllers\Backend\FeaturesController;
use App\Http\Controllers\Backend\PricingController;
use App\Http\Controllers\Frontend\CarController;
use App\Http\Controllers\Frontend\CashTransactionsController;
use App\Http\Controllers\Frontend\CustomerController;
use App\Http\Controllers\Frontend\DeletedServicesController;
use App\Http\Controllers\Frontend\DeviceBrandsController;
use App\Http\Controllers\Frontend\DeviceTypesController;
use App\Http\Controllers\Frontend\FeatureController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\HakkimizdaController;
use App\Http\Controllers\Frontend\ProductsController;
use App\Http\Controllers\Frontend\KatalogController;
use App\Http\Controllers\Frontend\FrontendContactController;
use App\Http\Controllers\Frontend\GenelAyarlarController;
use App\Http\Controllers\Frontend\IncomingCallsController;
use App\Http\Controllers\Frontend\InvoicesController;
use App\Http\Controllers\Frontend\OfferController;
use App\Http\Controllers\Frontend\PaymentMethodsController;
use App\Http\Controllers\Frontend\PaymentTypesController;
use App\Http\Controllers\Frontend\PersonelController;
use App\Http\Controllers\Frontend\PrimController;
use App\Http\Controllers\Frontend\ReceiptDesignController;
use App\Http\Controllers\Frontend\RoleController;
use App\Http\Controllers\Frontend\ServiceBatchPlanningController;
use App\Http\Controllers\Frontend\ServiceFormSetController;
use App\Http\Controllers\Frontend\ServiceReportsController;
use App\Http\Controllers\Frontend\ServiceResourceController;
use App\Http\Controllers\Frontend\ServicesController;
use App\Http\Controllers\Frontend\ServiceStagesController;
use App\Http\Controllers\Frontend\ServiceTimeController;
use App\Http\Controllers\Frontend\StageQuestionController;
use App\Http\Controllers\Frontend\StockCategoryController;
use App\Http\Controllers\Frontend\StockShelfController;
use App\Http\Controllers\Frontend\StockSupplierController;
use App\Http\Controllers\Frontend\StockController;
use App\Http\Controllers\Frontend\WarrantyPeriodController;
use App\Http\Controllers\Frontend\SurveyController;
use App\Http\Controllers\Frontend\StatisticController;
use App\Http\Controllers\Frontend\SubscriptionController;
use App\Http\Controllers\Frontend\TenantsController;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Frontend\ImpersonationController;
use App\Http\Controllers\Frontend\SuperAdminController;
use App\Http\Controllers\Frontend\SupportTicketController;
use App\Http\Controllers\Frontend\AdminSupportController;
use App\Http\Controllers\Frontend\DestekController;
use App\Http\Controllers\Frontend\ActivityLogController;
use App\Http\Controllers\Frontend\BulkSmsController;
use App\Http\Controllers\Frontend\IntegrationMarketplaceController;
use App\Http\Controllers\Frontend\SuperAdminInvoicesController;
use App\Http\Controllers\Frontend\PaymentHistoryController;
use App\Http\Controllers\Frontend\StorageController;
use App\Http\Controllers\Frontend\SuperAdminIntegrationController;
use App\Http\Controllers\Frontend\LegalContentController;
use App\Http\Controllers\Api\VerimorSantralController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\HipcallWebhookController;
use App\Http\Controllers\Frontend\TenantApiTokenController;
use App\Http\Controllers\Frontend\MarkaController;
use App\Http\Controllers\Frontend\ModellController;
use App\Http\Controllers\Frontend\ArizaKoduController;
use App\Services\ParasutService;
use App\Http\Controllers\FrontendHomeController;

/******************************************************************* PARAŞÜT DENEME ROUTLARI *************************************************************************************/
// Bağlantı testi
// Route::get('/parasut/test', function (ParasutService $parasut) {
//     $result = $parasut->testConnection();
//     return response()->json($result);
// });

// // Token göster
// Route::get('/parasut/token', function (ParasutService $parasut) {
//     try {
//         $token = $parasut->getAccessToken();
//         return response()->json([
//             'success' => true,
//             'token' => $token
//         ]);
//     } catch (Exception $e) {
//         return response()->json([
//             'success' => false,
//             'error' => $e->getMessage()
//         ]);
//     }
// });


// // Müşteri oluştur - TEST
// Route::get('/parasut/create-test-customer', function (ParasutService $parasut) {
//     $customerData = [
//         'email' => 'test@example.com',
//         'name' => 'Test Müşteri',
//         'short_name' => 'Test',
//         'contact_type' => 'person', // person veya company
//         'tax_number' => '11111111111', // TC Kimlik No (11 haneli)
//         'tax_office' => 'Test Vergi Dairesi',
//         'address' => 'Test Mahallesi Test Sokak No:1',
//         'city' => 'İstanbul',
//         'district' => 'Kadıköy',
//         'phone' => '5551234567',
//         'account_type' => 'customer' // Müşteri olduğunu belirtiyoruz
//     ];

//     $result = $parasut->createContact($customerData);

//     return response()->json($result);
// });


// // Fatura oluştur - TEST
// Route::get('/parasut/create-test-invoice', function (ParasutService $parasut) {
    
//     $contactId = '2047425'; // Az önce oluşturduğumuz müşteri ID'si
    
//     // Fatura kalemleri
//     $items = [
//         [
//             'description' => 'Teknik Servis Hizmeti',
//             'quantity' => 1,
//             'unit_price' => 500.00,
//             'vat_rate' => 20
//         ],
//         [
//             'description' => 'Yedek Parça',
//             'quantity' => 2,
//             'unit_price' => 150.00,
//             'vat_rate' => 20
//         ]
//     ];
    
//     // Fatura bilgileri
//     $invoiceData = [
//         'item_type' => 'invoice',
//         'description' => 'Test Servis Faturası',
//         'issue_date' => date('Y-m-d'),
//         'due_date' => date('Y-m-d', strtotime('+30 days')),
//         'currency' => 'TRL'
//     ];
    
//     $result = $parasut->createInvoice($contactId, $items, $invoiceData);
    
//     return response()->json($result);
// });

// Route::get('/parasut/create-test-product', function (ParasutService $parasut) {
    
//     // Ürün 1
//     $product1 = $parasut->createProduct([
//         'name' => 'Teknik Servis Hizmeti',
//         'code' => 'SRV-001',
//         'vat_rate' => 20,
//         'sales_excise_duty_code' => '',
//         'sales_invoice_details_count' => 0,
//         'unit' => 'Adet',
//         'communications_tax_rate' => 0,
//         'archived' => false,
//         'list_price' => 500,
//         'currency' => 'TRL',
//         'buying_price' => 0,
//         'inventory_tracking' => false
//     ]);
    
//     // Ürün 2
//     $product2 = $parasut->createProduct([
//         'name' => 'Yedek Parça',
//         'code' => 'YP-001',
//         'vat_rate' => 20,
//         'sales_excise_duty_code' => '',
//         'sales_invoice_details_count' => 0,
//         'unit' => 'Adet',
//         'communications_tax_rate' => 0,
//         'archived' => false,
//         'list_price' => 150,
//         'currency' => 'TRL',
//         'buying_price' => 0,
//         'inventory_tracking' => false
//     ]);
    
//     return response()->json([
//         'product1' => $product1,
//         'product2' => $product2
//     ]);
// });
// Route::get('/parasut/create-test-invoice', function (ParasutService $parasut) {
    
//     $contactId = '2047425'; // Müşteri ID
    
//     // Fatura kalemleri - ŞİMDİ ÜRÜN ID'LERİYLE
//     $items = [
//         [
//             'product_id' => '252426', // Teknik Servis Hizmeti
//             'description' => 'Teknik Servis Hizmeti',
//             'quantity' => 1,
//             'unit_price' => 500.00,
//             'vat_rate' => 20
//         ],
//         [
//             'product_id' => '252427', // Yedek Parça
//             'description' => 'Yedek Parça',
//             'quantity' => 2,
//             'unit_price' => 150.00,
//             'vat_rate' => 20
//         ]
//     ];
    
//     // Fatura bilgileri
//     $invoiceData = [
//         'item_type' => 'invoice',
//         'description' => 'Test Servis Faturası',
//         'issue_date' => date('Y-m-d'),
//         'due_date' => date('Y-m-d', strtotime('+30 days')),
//         'currency' => 'TRL'
//     ];
    
//     $result = $parasut->createInvoice($contactId, $items, $invoiceData);
    
//     return response()->json($result);
// });

// // E-Arşiv oluştur - TEST
// Route::get('/parasut/create-test-e-archive', function (ParasutService $parasut) {
    
//     $invoiceId = '2346112'; // Az önce oluşturduğumuz fatura
    
//     // İnternet satışı bilgileri (opsiyonel)
//     $internetSale = [
//         'url' => 'https://serbis.example.com',
//         'payment_type' => 'ODEMEARACISI', // KREDIKARTI/BANKAKARTI, EFT/HAVALE, KAPIDAODEME, ODEMEARACISI
//         'payment_platform' => 'Serbis Ödeme Sistemi',
//         'payment_date' => date('Y-m-d')
//     ];
    
//     $result = $parasut->createEArchive($invoiceId, $internetSale);
    
//     return response()->json($result);
// });

// // VKN Kontrol - TEST
// Route::get('/parasut/check-vkn/{taxNumber}', function (ParasutService $parasut, $taxNumber) {
    
//     $result = $parasut->checkVknType($taxNumber);
    
//     if ($result['is_e_invoice']) {
//         return response()->json([
//             'message' => 'Bu VKN e-Fatura sisteminde kayıtlı',
//             'data' => $result
//         ]);
//     } else {
//         return response()->json([
//             'message' => 'Bu VKN e-Fatura sisteminde kayıtlı değil, E-Arşiv kullanılmalı',
//             'data' => $result
//         ]);
//     }
// });
// // Job durumu kontrol - TEST
// Route::get('/parasut/check-job/{jobId}', function (ParasutService $parasut, $jobId) {
    
//     $result = $parasut->checkJobStatus($jobId);
    
//     return response()->json($result);
// });
/******************************************************************* PARAŞÜT DENEME ROUTLARI *************************************************************************************/


Route::get('/secure', function () {
    return view('backend.index');
})->middleware(['auth', 'verified'])->name('dashboard');

//Admin login logout routes
Route::controller(UserController::class)->group(function () {
    //Route::get('/register', 'register')->name('register');
    //Route::post('/register', 'register_action')->name('register.action');

    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'login_action')->name('login.action');
});

// Kullanıcı destek talepleri
Route::middleware(['auth','check.tenant.status'])->prefix('{tenant_id}')->group(function () {
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index')->middleware(['permission:Destek Taleplerini Görebilir']);
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store')->middleware('check.storage');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply')->middleware('check.storage');
    Route::get('/support/{ticket}/download/{fileName}', [SupportTicketController::class, 'downloadAttachment'])->name('support.download');
});

Route::middleware(['auth'])->prefix('super-admin')->name('super.admin.')->group(function () {
    Route::get('/destek', [AdminSupportController::class, 'index'])->name('destek.index');
    Route::get('/destek/dashboard', [AdminSupportController::class, 'dashboard'])->name('destek.dashboard');
    Route::get('/destek/{ticket}', [AdminSupportController::class, 'show'])->name('destek.show');
    Route::post('/destek/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('destek.reply');
    Route::patch('/destek/{ticket}/close', [AdminSupportController::class, 'close'])->name('destek.close');
    Route::patch('/destek/{ticket}/reopen', [AdminSupportController::class, 'reopen'])->name('destek.reopen');
    Route::get('/destek/{ticket}/download/{fileName}', [AdminSupportController::class, 'downloadAttachment'])->name('destek.download');
});
//Route::get('super-admin/destek', [DestekController::class, 'index'])->name('destek.index');
//İmpersonation 
    // Impersonation Routes
    Route::prefix('impersonation')->name('impersonation.')->group(function () {
        // Kullanıcı kimliğini kullanmaya başla
        Route::post('/start/{user_id}', [ImpersonationController::class, 'start'])
             ->name('start');
        // Impersonation'ı sonlandır
        Route::post('/stop', [ImpersonationController::class, 'stop'])
             ->name('stop');
        // Impersonate edilebilir kullanıcıları getir (AJAX)
        Route::get('/users/{tenant_id}', [ImpersonationController::class, 'getUsersForImpersonation'])
             ->name('users');
        // Impersonation geçmişi (AJAX)
        Route::get('/history', [ImpersonationController::class, 'getImpersonationHistory'])
             ->name('history');
        // Mevcut impersonation durumunu kontrol et (AJAX)
        Route::get('/status', [ImpersonationController::class, 'checkStatus'])
             ->name('status');
    });
// Super Admin Routes
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::prefix('super-admin')->name('super.admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])
             ->name('dashboard');
        
        // Tüm firmalar yönetimi
        Route::get('/tenants', [SuperAdminController::class, 'allTenants'])
             ->name('tenants');
        
        // Firma düzenleme
        Route::get('/tenant/{id}/edit', [SuperAdminController::class, 'editTenant'])
             ->name('tenant.edit');
        
        Route::post('/tenant/{id}/update', [SuperAdminController::class, 'updateTenant'])
             ->name('tenant.update');
        
        // Firma durum değiştirme
        Route::post('/tenant/{id}/toggle-status', [SuperAdminController::class, 'changeTenantStatus'])
             ->name('tenant.toggle.status');

        // Firma silme (pasif hale getirme)
        Route::get('/tenant/{id}/delete', [SuperAdminController::class, 'deleteTenant'])
             ->name('tenant.delete');
    
        Route::get('/log-kayitlari', [ActivityLogController::class, 'superAdminIndex'])->name('super.admin.activity.logs');
        Route::get('/activity-logs/data', [ActivityLogController::class, 'getLogs'])->name('super.admin.activity.logs.data');
        Route::post('/activity-logs/clear', [ActivityLogController::class, 'clearLogs'])->name('super.admin.activity.logs.clear');


        // Super Admin Faturalar
        Route::controller(SuperAdminInvoicesController::class)->group(function () {
            Route::get('/faturalar', 'AllInvoice')->name('invoices');
            Route::get('/fatura/ekle', 'AddInvoice')->name('invoices.add');
            Route::post('/fatura/gonder', 'StoreInvoice')->name('invoices.store');
            Route::get('/fatura/duzenle/{id}', 'EditInvoice')->name('invoices.edit');
            Route::post('/fatura/guncelle', 'UpdateInvoice')->name('invoices.update');
            Route::get('/fatura/sil/{id}', 'DeleteInvoice')->name('invoices.delete');

            Route::get('/fatura/goruntule/{id}', 'ShowInvoice')->name('invoices.show');
            Route::post('/fatura/yukle', 'UploadInvoice')->name('invoices.upload');
            Route::post('/eArsiv/sil/{id}', 'DeleteEinvoice')->name('invoices.delete.einvoice');

            Route::get('/fatura-sonuc', 'GetInvoices')->name('invoices.get');
            Route::post('/firma-ara', [SuperAdminInvoicesController::class, 'FirmaAra'])->name('firma.ara');
            
            Route::get('/invoices/payments', [SuperAdminInvoicesController::class, 'GetCompletedPayments'])->name('invoices.payments');
        });

         // Ödeme bilgileri route'ları
        Route::get('/tenant/{id}/payments', [SuperAdminController::class, 'getTenantPayments'])
            ->name('tenant.payments');
        
        Route::get('/tenant/{tenantId}/payment/{paymentType}/{paymentId}', [SuperAdminController::class, 'getPaymentDetail'])
            ->name('tenant.payment.detail');
        
        Route::get('/tenant/{tenantId}/payment-statistics', [SuperAdminController::class, 'getPaymentStatistics'])
            ->name('tenant.payment.statistics');

        Route::get('/tenant/{id}/storage-details', [SuperAdminController::class, 'getStorageDetails'])->name('tenant.storage.details');

        Route::get('/invoices/tenants-with-pending-payments', [SuperAdminInvoicesController::class, 'GetTenantsWithPendingPayments'])
       ->name('invoices.tenants.pending');

               // Tüm Firmaların Ödeme Geçmişi
        Route::prefix('payment-history')->name('payment.history.')->group(function () {
            Route::get('/', [SuperAdminController::class, 'allPaymentHistory'])
                ->name('index');
            Route::get('/export', [SuperAdminController::class, 'exportAllPayments'])
                ->name('export');
            Route::get('/tenant/{tenant_id}', [SuperAdminController::class, 'tenantPaymentHistory'])
                ->name('tenant');
        });
        Route::get('/payment-history/totals', [SuperAdminController::class, 'getPaymentTotals'])
       ->name('payment.history.totals');
       

       Route::controller(SuperAdminIntegrationController::class)->group(function () {
        Route::get('/entegrasyonlar', 'AllIntegrations')->name('integrations');
        Route::get('/entegrasyon/ekle', 'AddIntegration')->name('integration.add');
        Route::post('/entegrasyon/gonder', 'StoreIntegration')->name('integration.store');
        Route::get('/entegrasyon/duzenle/{id}', 'EditIntegration')->name('integration.edit');
        Route::post('/entegrasyon/guncelle/{id}', 'UpdateIntegration')->name('integration.update');
        Route::get('/entegrasyon/sil/{id}', 'DeleteIntegration')->name('integration.delete');

    });

    // Frontend Yönetimi
    Route::prefix('frontend')->name('frontend.')->group(function () {
        Route::get('/', [SuperAdminController::class, 'frontendSettings'])->name('index');
        Route::get('/anasayfa', [SuperAdminController::class, 'homeSettings'])->name('home');

        // İstatistikler
        Route::get('/home/stat/{id}', [SuperAdminController::class, 'getStat'])->name('home.stat.get');
        Route::post('/home/stat', [SuperAdminController::class, 'storeStat'])->name('home.stat.store');
        Route::put('/home/stat/{id}', [SuperAdminController::class, 'updateStat'])->name('home.stat.update');
        Route::delete('/home/stat/{id}', [SuperAdminController::class, 'deleteStat'])->name('home.stat.delete');

        // Modüller
        Route::get('/home/module/{id}', [SuperAdminController::class, 'getModule'])->name('home.module.get');
        Route::post('/home/module', [SuperAdminController::class, 'storeModule'])->name('home.module.store');
        Route::put('/home/module/{id}', [SuperAdminController::class, 'updateModule'])->name('home.module.update');
        Route::delete('/home/module/{id}', [SuperAdminController::class, 'deleteModule'])->name('home.module.delete');

        // Sektörler
        Route::get('/home/sector/{id}', [SuperAdminController::class, 'getSector'])->name('home.sector.get');
        Route::post('/home/sector', [SuperAdminController::class, 'storeSector'])->name('home.sector.store');
        Route::post('/home/sector/{id}', [SuperAdminController::class, 'updateSector'])->name('home.sector.update');
        Route::delete('/home/sector/{id}', [SuperAdminController::class, 'deleteSector'])->name('home.sector.delete');

        // Entegrasyonlar
        Route::get('/home/integration/{id}', [SuperAdminController::class, 'getIntegration'])->name('home.integration.get');
        Route::post('/home/integration', [SuperAdminController::class, 'storeIntegration'])->name('home.integration.store');
        Route::put('/home/integration/{id}', [SuperAdminController::class, 'updateIntegration'])->name('home.integration.update');
        Route::delete('/home/integration/{id}', [SuperAdminController::class, 'deleteIntegration'])->name('home.integration.delete');

        // Yorumlar
        Route::get('/home/testimonial/{id}', [SuperAdminController::class, 'getTestimonial'])->name('home.testimonial.get');
        Route::post('/home/testimonial', [SuperAdminController::class, 'storeTestimonial'])->name('home.testimonial.store');
        Route::put('/home/testimonial/{id}', [SuperAdminController::class, 'updateTestimonial'])->name('home.testimonial.update');
        Route::delete('/home/testimonial/{id}', [SuperAdminController::class, 'deleteTestimonial'])->name('home.testimonial.delete');

        // SSS
        Route::get('/home/faq/{id}', [SuperAdminController::class, 'getFaq'])->name('home.faq.get');
        Route::post('/home/faq', [SuperAdminController::class, 'storeFaq'])->name('home.faq.store');
        Route::put('/home/faq/{id}', [SuperAdminController::class, 'updateFaq'])->name('home.faq.update');
        Route::delete('/home/faq/{id}', [SuperAdminController::class, 'deleteFaq'])->name('home.faq.delete');

        // Ana Sayfa İçerik Yönetimi
        Route::get('/icerik', [SuperAdminController::class, 'homepageContent'])->name('content');
        Route::post('/icerik/guncelle', [SuperAdminController::class, 'updateHomepageContent'])->name('content.update');

        // Navigation Yönetimi
        Route::get('/navigation', [SuperAdminController::class, 'navigationSettings'])->name('navigation');

        // Yasal Sayfalar
        Route::get('/legal-pages', [SuperAdminController::class, 'legalPages'])->name('legal-pages');
        Route::get('/legal-pages/create', [SuperAdminController::class, 'editLegalPage'])->name('legal-pages.create');
        Route::get('/legal-pages/{section}/edit', [SuperAdminController::class, 'editLegalPage'])->name('legal-pages.edit');
        Route::post('/legal-pages', [SuperAdminController::class, 'storeLegalPage'])->name('legal-pages.store');
        Route::delete('/legal-pages/{section}', [SuperAdminController::class, 'deleteLegalPage'])->name('legal-pages.delete');


        // Hakkımızda İçerik Yönetimi    
        Route::get('/about-content', [SuperAdminController::class, 'aboutContent'])->name('about-content');
        Route::post('/about-content', [SuperAdminController::class, 'updateAboutContent'])->name('about-content.update');

        // Sektörler Yönetimi
        Route::get('/sectors-content', [SuperAdminController::class, 'sectorsContent'])->name('sectors-content');
        Route::post('/sectors-content', [SuperAdminController::class, 'updateSectorsContent'])->name('sectors-content.update');
        // Sektör Detay Yönetimi
        Route::get('/sector-detail/{slug}', [SuperAdminController::class, 'sectorDetail'])->name('sector-detail');
        Route::post('/sector-detail/{slug}', [SuperAdminController::class, 'updateSectorDetail'])->name('sector-detail.update');

        // Özellikler Yönetimi
        Route::get('/features-content', [SuperAdminController::class, 'featuresContent'])->name('features-content');
        Route::post('/features-content', [SuperAdminController::class, 'updateFeaturesContent'])->name('features-content.update');
        // Özellik Detay Yönetimi
        Route::get('/feature-detail/{slug}', [SuperAdminController::class, 'featureDetail'])->name('feature-detail');
        Route::post('/feature-detail/{slug}', [SuperAdminController::class, 'updateFeatureDetail'])->name('feature-detail.update');

        // Entegrasyonlar
        Route::get('/integrations-content', [SuperAdminController::class, 'integrationsContent'])->name('integrations-content');
        Route::post('/integrations-content', [SuperAdminController::class, 'updateIntegrationsContent'])->name('integrations-content.update');

        // Fiyatlandırma
        Route::get('/pricing-content', [SuperAdminController::class, 'pricingContent'])->name('pricing-content');
        Route::post('/pricing-content', [SuperAdminController::class, 'updatePricingContent'])->name('pricing-content.update');

        // İletişim
        Route::get('/contact-content', [SuperAdminController::class, 'contactContent'])->name('contact-content');
        Route::post('/contact-content', [SuperAdminController::class, 'updateContactContent'])->name('contact-content.update');


        });

    });
    
});
// Arıza Kodları Yönetimi Route'ları - middleware'i 'superadmin' olarak değiştirin
Route::middleware(['auth', 'superadmin'])->prefix('super-admin')->name('super.admin.')->group(function () {
    
    // Markalar
    Route::get('/markalar', [MarkaController::class, 'index'])->name('markalar.index');
    Route::get('/marka-ekle', [MarkaController::class, 'create'])->name('markalar.create');
    Route::post('/marka-ekle', [MarkaController::class, 'store'])->name('markalar.store');
    Route::get('/marka-duzenle/{id}', [MarkaController::class, 'edit'])->name('markalar.edit');
    Route::post('/marka-duzenle/{id}', [MarkaController::class, 'update'])->name('markalar.update');
    Route::post('/marka-sil/{id}', [MarkaController::class, 'destroy'])->name('markalar.destroy');
    
    // Modeller
    Route::get('/modeller/{marka_id}', [ModellController::class, 'index'])->name('modeller.index');
    Route::get('/model-ekle/{marka_id}', [ModellController::class, 'create'])->name('modeller.create');
    Route::post('/model-ekle', [ModellController::class, 'store'])->name('modeller.store');
    Route::get('/model-duzenle/{id}', [ModellController::class, 'edit'])->name('modeller.edit');
    Route::post('/model-duzenle/{id}', [ModellController::class, 'update'])->name('modeller.update');
    Route::post('/model-sil/{id}', [ModellController::class, 'destroy'])->name('modeller.destroy');
    
    // Arıza Kodları
    Route::get('/kodlar', [ArizaKoduController::class, 'index'])->name('kodlar.index');
    Route::get('/kod-ekle', [ArizaKoduController::class, 'create'])->name('kodlar.create');
    Route::post('/kod-ekle', [ArizaKoduController::class, 'store'])->name('kodlar.store');
    Route::get('/kod-duzenle/{id}', [ArizaKoduController::class, 'edit'])->name('kodlar.edit');
    Route::post('/kod-duzenle/{id}', [ArizaKoduController::class, 'update'])->name('kodlar.update');
    Route::post('/kod-sil/{id}', [ArizaKoduController::class, 'destroy'])->name('kodlar.destroy');
    
    // API: Kod arama (AJAX için)
    Route::post('/ariza-kodu-ara', [ArizaKoduController::class, 'search'])->name('kodlar.search');
});
Route::group(['prefix' => '{tenant_id}', 'middleware' => ['auth']], function () {
    // Activity Logs
    Route::get('/log-kayitlari', [ActivityLogController::class, 'index'])->name('activity.logs.index');
    Route::get('/activity-logs/data', [ActivityLogController::class, 'getLogs'])->name('activity.logs.data');
});

Route::middleware(['auth'])->group(function () {

    Route::controller(AdminController::class)->group(function () {
        Route::get('/admin/logout', 'destroy')->name('admin.logout');
        Route::get('/admin/profile', 'Profile')->name('admin.profile');
        Route::get('/edit/profile', 'EditProfile')->name('edit.profile');
        Route::post('/store/profile', 'StoreProfile')->name('store.profile');
    });
});

//Anasayfa Slider
Route::middleware(['auth'])->group(function () {
    Route::controller(HomeSliderController::class)->group(function () {
        Route::get('/home/image', 'HomeImage')->name('home.image');
        Route::get('/add/slide', 'AddSlide')->name('add.slide');
        Route::post('/store/slide', 'StoreSlide')->name('store.slide');
        Route::get('/edit/slide/{id}', 'EditSlide')->name('edit.slide');
        Route::post('/update/slide', 'UpdateSlide')->name('update.slide');
        Route::get('/delete/slide/{id}', 'DeleteSlide')->name('delete.slide');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(AboutController::class)->group(function () {
        Route::get('/all/about', 'AllAbout')->name('all.about');
        Route::post('/update/about', 'UpdateAbout')->name('update.about');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(PrivacyPolicyController::class)->group(function () {
        Route::get('/all/privacy', 'AllPrivacy')->name('all.privacy');
        Route::post('/update/privacy', 'UpdatePrivacy')->name('update.privacy');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(MisyonController::class)->group(function () {
        Route::get('/all/misyon', 'AllMisyon')->name('all.misyon');
        Route::post('/update/misyon', 'UpdateMisyon')->name('update.misyon');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(SettingsController::class)->group(function () {
        Route::get('/site/settings', 'SiteSettings')->name('site.settings');
        Route::post('/update/site/settings', 'UpdateSiteSettings')->name('update.site.settings');

        Route::get('/email/settings', 'EmailSettings')->name('email.settings');
        Route::post('/update/email/settings', 'UpdateEmailSettings')->name('update.email.settings');

        Route::get('/google/settings', 'GoogleSettings')->name('google.settings');
        Route::post('/update/google/settings', 'UpdateGoogleSettings')->name('update.google.settings');

        Route::get('/company/settings', 'CompanySettings')->name('company.settings');
        Route::post('/update/company/settings', 'UpdateCompanySettings')->name('update.company.settings');

        Route::get('/social/media/settings', 'SocialMediaSettings')->name('social.media.settings');
        Route::post('/update/socialmedia/settings', 'UpdateSocialMediaSettings')->name('update.socialmedia.settings');
    });
});



//Anasayfa servislerimiz bölümü
Route::middleware(['auth'])->group(function () {
    Route::controller(HomeCardController::class)->group(function () {
        Route::get('/all/home/card', 'AllHomeCard')->name('all.home.card');
        Route::get('/add/home/card', 'AddHomeCard')->name('add.home.card');
        Route::post('/store/home/card', 'StoreHomeCard')->name('store.home.card');
        Route::get('/edit/home/card/{id}', 'EditHomeCard')->name('edit.home.card');
        Route::post('/update/home/card', 'UpdateHomeCard')->name('update.home.card');
        Route::get('/delete/home/card/{id}', 'DeleteHomeCard')->name('delete.home.card');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(RoomController::class)->group(function () {
        Route::get('/all/project', 'AllRoom')->name('all.room');
        Route::get('/add/project', 'AddRoom')->name('add.room');
        Route::post('/store/project', 'StoreRoom')->name('store.room');
        Route::get('/edit/project/{id}', 'EditRoom')->name('edit.room');
        Route::post('/update/project', 'UpdateRoom')->name('update.room');
        Route::get('/delete/project/{id}', 'DeleteRoom')->name('delete.room');
    });
});

Route::middleware(['auth'])->group(function() {
    Route::controller(CategoriesController::class)->group(function() {
        Route::get('/all/categories', 'AllCategories')->name('all.categories');
        Route::get('/add/categories', 'AddCategories')->name('add.categories');
        Route::post('/store/categories', 'StoreCategories')->name('store.categories');
        Route::get('/edit/categories/{id}', 'EditCategories')->name('edit.categories');
        Route::post('/update/categories', 'UpdateCategories')->name('update.categories');
        Route::get('/delete/categories/{id}', 'DeleteCategories')->name('delete.categories');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(CategoryImagesController::class)->group(function () {
        Route::get('/all/category/image/{id}', 'AllCategoryImage')->name('all.category.image');
        Route::get('/add/category/image/{id}', 'AddCategoryImage')->name('add.category.image');
        Route::post('/store/category/image', 'StoreCategoryImage')->name('store.category.image');
        Route::get('/delete/category/image/{id}', 'DeleteCategoryImage')->name('delete.category.image');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(MenusController::class)->group(function () {
        Route::get('/all/menus', 'AllMenus')->name('all.menus');
        Route::get('/add/menus', 'AddMenus')->name('add.menus');
        Route::post('/store/menus', 'StoreMenus')->name('store.menus');
        Route::get('/edit/menus/{id}', 'EditMenus')->name('edit.menus');
        Route::post('/update/menus/{id}', 'UpdateMenus')->name('update.menus');
        Route::get('/delete/menus/{id}', 'DeleteMenus')->name('delete.menus');
    });
});

//Şirket müşteri yorumları kısmı
Route::middleware(['auth'])->group(function () {
    Route::controller(ClientsController::class)->group(function () {
        Route::get('/all/client', 'AllClient')->name('all.client');
        Route::get('/add/client', 'AddClient')->name('add.client');
        Route::post('/store/client', 'StoreClient')->name('store.client');
        Route::get('/edit/client/{id}', 'EditClient')->name('edit.client');
        Route::post('/update/client', 'UpdateClient')->name('update.client');
        Route::get('/delete/client/{id}', 'DeleteClient')->name('delete.client');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(GalleryController::class)->group(function () {
        Route::get('/all/gallery', 'AllGallery')->name('all.gallery');
        Route::get('/add/gallery', 'AddGallery')->name('add.gallery');
        Route::post('/store/gallery', 'StoreGallery')->name('store.gallery');
        Route::post('/store/sort', 'StoreSort')->name('store.sort');
        Route::get('/edit/gallery/{id}', 'EditGallery')->name('edit.gallery');
        Route::post('/update/gallery', 'UpdateGallery')->name('update.gallery');
        Route::get('/delete/gallery/{id}', 'DeleteGallery')->name('delete.gallery');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(FaqController::class)->group(function () {
        Route::get('/all/faq', 'AllFaq')->name('all.faq');
        Route::get('/add/faq', 'AddFaq')->name('add.faq');
        Route::post('/store/faq', 'StoreFaq')->name('store.faq');
        Route::get('/edit/faq/{id}', 'EditFaq')->name('edit.faq');
        Route::post('/update/faq', 'UpdateFaq')->name('update.faq');
        Route::get('/delete/faq/{id}', 'DeleteFaq')->name('delete.faq');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(ReferencesController::class)->group(function () {
        Route::get('/all/references', 'AllReferences')->name('all.references');
        Route::get('/add/references', 'AddReferences')->name('add.references');
        Route::post('/store/references', 'StoreReferences')->name('store.references');
        Route::post('/store/references/sort', 'StoreReferencesSort')->name('store.references.sort');
        Route::get('/edit/references/{id}', 'EditReferences')->name('edit.references');
        Route::post('/update/references', 'UpdateReferences')->name('update.references');
        Route::get('/delete/references/{id}', 'DeleteReferences')->name('delete.references');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(RoomImageController::class)->group(function () {
        Route::get('/all/project/image/{id}', 'AllRoomImage')->name('all.room.image');
        Route::get('/add/project/image/{id}', 'AddRoomImage')->name('add.room.image');
        Route::post('/store/project/image', 'StoreRoomImage')->name('store.room.image');
        Route::get('/edit/project/image/{id}', 'EditRoomImage')->name('edit.room.image');
        Route::post('/update/project/image', 'UpdateRoomImage')->name('update.room.image');
        Route::get('/delete/project/image/{id}', 'DeleteRoomImage')->name('delete.room.image');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(RoomFacilityController::class)->group(function () {
        Route::get('/all/room/facility', 'AllRoomFacility')->name('all.room.facility');
        Route::get('/add/room/facility', 'AddRoomFacility')->name('add.room.facility');
        Route::post('/store/room/facility', 'StoreRoomFacility')->name('store.room.facility');
        Route::get('/edit/room/facility/{id}', 'EditRoomFacility')->name('edit.room.facility');
        Route::post('/update/room/facility', 'UpdateRoomFacility')->name('update.room.facility');
        Route::get('/delete/room/facility/{id}', 'DeleteRoomFacility')->name('delete.room.facility');
    });
});

//İletişim sayfası
Route::middleware(['auth'])->group(function () {
    Route::controller(ContactController::class)->group(function () {
        Route::get('/contact/message', 'ContactMessage')->name('contact.message');
        Route::get('/delete/message/{id}', 'DeleteMessage')->name('delete.message');
    });
});

//Sayfa ayarları
Route::middleware(['auth'])->group(function () {
    Route::controller(PagesController::class)->group(function () {
        Route::get('/pages', 'Pages')->name('pages');
        Route::get('/pages/home', 'PagesHome')->name('pages.home');
        Route::post('/update/pages/home', 'UpdatePagesHome')->name('update.pages.home');

        Route::get('/pages/about', 'PagesAbout')->name('pages.about');
        Route::post('/update/pages/about', 'UpdatePagesAbout')->name('update.pages.about');

        Route::get('/pages/projects', 'PagesRooms')->name('pages.rooms');
        Route::post('/update/pages/projects', 'UpdatePagesRoom')->name('update.pages.rooms');

        Route::get('/pages/gallery', 'PagesGallery')->name('pages.gallery');
        Route::post('/update/pages/gallery', 'UpdatePagesGallery')->name('update.pages.gallery');

        Route::get('/pages/contact', 'PagesContact')->name('pages.contact');
        Route::post('/update/pages/contact', 'UpdatePagesContact')->name('update.pages.contact');

        Route::get('/pages/products', 'PagesProducts')->name('pages.products');
        Route::post('/update/pages/products', 'UpdatePagesProducts')->name('update.pages.products');

        Route::get('/pages/misyon', 'PagesMisyon')->name('pages.misyon');
        Route::post('/update/pages/misyon', 'UpdatePagesMisyon')->name('update.pages.misyon');

        Route::get('/pages/references', 'PagesReferences')->name('pages.references');
        Route::post('/update/pages/references', 'UpdatePagesReferences')->name('update.pages.references');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::get('/all/product', 'AllProduct')->name('all.product');
        Route::get('/add/product', 'AddProduct')->name('add.product');
        Route::post('/store/product', 'StoreProduct')->name('store.product');
        Route::get('/edit/product/{id}', 'EditProduct')->name('edit.product');
        Route::post('/update/product', 'UpdateProduct')->name('update.product');
        Route::get('/delete/product/{id}', 'DeleteProduct')->name('delete.product');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(ProductImageController::class)->group(function () {
        Route::get('/all/product/image/{id}', 'AllProductImage')->name('all.product.image');
        Route::get('/add/product/image/{id}', 'AddProductImage')->name('add.product.image');
        Route::post('/store/product/image', 'StoreProductImage')->name('store.product.image');
        Route::get('/edit/product/image/{id}', 'EditProductImage')->name('edit.product.image');
        Route::post('/update/product/image', 'UpdateProductImage')->name('update.product.image');
        Route::get('/delete/product/image/{id}', 'DeleteProductImage')->name('delete.product.image');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(DocumentsController::class)->group(function () {
        Route::get('/all/documents', 'AllDocuments')->name('all.documents');
        Route::post('/update/documents', 'UpdateDocuments')->name('update.documents');
        Route::get('/delete/documents/{id}', 'DeleteDocuments')->name('delete.documents');

    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(PricingController::class)->group(function () {
        Route::get('/all/pricing', 'AllPricing')->name('all.pricing');
        Route::get('/add/pricing', 'AddPricing')->name('add.pricing');
        Route::post('/store/pricing', 'StorePricing')->name('store.pricing');
        Route::get('/edit/pricing/{id}', 'EditPricing')->name('edit.pricing');
        Route::post('/update/pricing', 'UpdatePricing')->name('update.pricing');
        Route::get('/delete/pricing/{id}', 'DeletePricing')->name('delete.pricing');
    });
});

Route::middleware(['auth'])->group(function() {
    Route::controller(FeaturesController::class)->group(function() {
        Route::get('/all/features', 'AllFeatures')->name('all.features');
        Route::get('/add/features', 'AddFeatures')->name('add.features');
        Route::post('/store/features', 'StoreFeatures')->name('store.features');
        Route::get('/edit/features/{id}', 'EditFeatures')->name('edit.features');
        Route::post('/update/features', 'UpdateFeatures')->name('update.features');
        Route::get('/delete/features/{id}', 'DeleteFeatures')->name('delete.features');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(FeatureImagesController::class)->group(function () {
        Route::get('/all/features/image/{id}', 'AllFeaturesImage')->name('all.features.image');
        Route::get('/add/features/image/{id}', 'AddFeaturesImage')->name('add.features.image');
        Route::post('/store/features/image', 'StoreFeaturesImage')->name('store.features.image');
        Route::get('/edit/features/image/{id}', 'EditFeaturesImage')->name('edit.features.image');
        Route::post('/update/features/image', 'UpdateFeaturesImage')->name('update.features.image');
        Route::get('/delete/features/image/{id}', 'DeleteFeaturesImage')->name('delete.features.image');
    });
});


// Yasal Sayfalar
Route::get('/{slug}', [HomeController::class, 'legalPage'])
    ->where('slug', 'gizlilik|kullanim-kosullari|kvkk')
    ->name('legal.page');
Route::controller(HomeController::class)->group(function() {
    Route::get('/', 'Index')->name('home');
    Route::get('/hakkimizda', 'About')->name('about'); 
    Route::get('/sektorler', 'Sectors')->name('sectors');
    Route::get('/sektor/{slug}', 'SectorDetail')->name('sector.detail');
    Route::get('/ozellikler', 'Features')->name('features');
    Route::get('/ozellik/{slug}', 'FeatureDetail')->name('feature.detail');
    Route::get('/entegrasyonlar', 'Integrations')->name('integrations');
    Route::get('/fiyatlar', 'Pricing')->name('pricing');
    Route::get('/iletisim', [HomeController::class, 'Contact'])->name('contact_frontend');
    Route::post('/iletisim', [HomeController::class, 'ContactSubmit'])->name('contact.submit');
    // Plan seçimi route'u
    Route::get('/select-plan/{planIndex}', [HomeController::class, 'selectPlan'])->name('select.plan');
    



    //Eski kodlar
    Route::get('/pricing', 'Pricing')->name('pricing');
    Route::get('/select-plan/{plan}', 'select')->name('plan.select');
    Route::get('/kullanici-kaydi', 'Register')->name('kayit');
    Route::post('/register-action', 'RegisterAction')->name('kayit.action');
    Route::post('/validate-step', 'validateStep')->name('validate.step');
    Route::get('/get-subscription-plans', 'getSubscriptionPlans')->name('get.subscription.plans');
    // Yeni SMS doğrulama rotaları
    Route::post('/sms-dogrulama', 'verifySmsCode')->name('sms.verification.verify');
    Route::get('/kayit-basarili', 'RegisterSuccess')->name('register.success');

    Route::get('/kullanici-girisi', 'Login')->name('giris');
    Route::post('/login-action', 'LoginAction')->name('giris.action');

    Route::get('/logout', 'logout')->name('logout');
    Route::get('/get-states/{countryId}', 'getStatesByCountry')->name('get.states');

    //Şifre Sıfırlama rotaları
    Route::get('/sifremi-unuttum', 'showForgotPasswordForm')->name('password.request');
    Route::post('/sifre-sifirlama-talebi', 'sendResetLinkEmail')->name('password.email');
    Route::get('/sifre-sifirla/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/sifre-sifirla', 'resetPassword')->name('password.update');

    // İl ve İlçe listeleme route'ları
    Route::get('/get-cities', 'getCities')->name('get.cities');
    Route::get('/get-districts', 'getDistricts')->name('get.districts');
    Route::get('/get-sectors', 'getSectors')->name('get.sectors');
});

Route::group(['prefix' => '{tenant_id}', 'middleware' => ['auth','checkTenantId','check.subscription','redirect_after_login','check.tenant.status']], function () {
    Route::controller(HomeController::class)->group(function() {
        Route::get('/dashboard', 'Dashboard')->name('secure.home');
        Route::get('/dashboard/stats',  'getStats')->name('dashboard.stats');
        Route::get('/dashboard/chart-data',  'getChartData')->name('dashboard.chart');
    });

    Route::controller(SurveyController::class)->group(function() {
        Route::get('/anket/{servisId}/create', 'SurveyCreate')->name('survey.create');
        Route::post('/anket/{servisId}/store', 'SurveyStore')->name('survey.store');
        Route::get('/anket-rapor-modal', 'SurveyReports')->name('survey.reports');
    });

    Route::controller(StatisticController::class)->group(function() {

        //Service Statistics
        Route::get('/istatistikler', 'ServiceStatistics')->name('statistics');
        Route::get('/chart-data', 'getChartDataAjax')->name('statistics.chart.data');
        Route::get('/hourly-data', 'getHourlyDataAjax')->name('statistics.hourly.data');
        //Technician Statistics
        Route::get('/teknisyen-istatistikleri', 'TechnicianStatistics')->name('technician.statistics');
        Route::post('/teknisyen-istatistikleri/data', 'getTechnicianStatisticsData')->name('technician.statistics.data');
        Route::post('/teknisyen-istatistikleri/detail', 'getTechnicianDetailStatistics')->name('technician.statistics.detail');
        Route::post('/teknisyen-detay/data', 'getTechnicianDetailData');
        //Operator Statistics
        Route::get('/operator-istatistikleri', 'OperatorStatistics')->name('operator.statistics');
        Route::post('/operator-istatistikleri/data', 'getOperatorStatisticsData')->name('operator.statistics.data');
        //State Statistics
        Route::get('/durum-istatistikleri', 'StateStatistics')->name('state.statistics');
        //Stage Statistics
        Route::get('/asama-istatistikleri', 'StageStatistics')->name('stage.statistics');
        //Stocks Statistics
        Route::get('/depo-istatistikleri', 'StockStatistics')->name('stock.statistics');
        Route::post('/depo-istatistikleri/data', 'getPersonelDepoData')->name('stock.statistics.data');
        //Ilce Statistics
        Route::get('/ilçe-istatistikleri', 'IlceStatistics')->name('ilce.statistics');
        //Survey Statistics
        Route::get('/anket-istatistikleri', 'SurveyStatistics')->name('survey.statistics');
        Route::post('/anket-istatistikleri/data', 'getSurveyStatisticsData')->name('survey.statistics.data');
        Route::post('/anket-sonuclari/data',  'getSurveyResults')->name('survey.results.data');
        //Cash Statistics
        Route::get('/kasa-istatistikleri', 'CashStatistics')->name('cash.statistics');
        Route::post('/gelir-tablo/getir', 'kasaFilteredData')->name('cash.income.data');
        Route::post('/gider-tablo/getir', 'giderTabloGetir')->name('cash.expense.table');
        Route::post('/gelir-grafik/getir', 'gelirGrafikGetir')->name('cash.income.chart');
        Route::post('/gider-grafik/getir', 'giderGrafikGetir')->name('cash.expense.chart');
    });


    Route::controller(PersonelController::class)->group(function() {
        Route::get('/personeller', 'AllStaffs')->name('staffs');
        Route::get('/personel-ekle', 'AddStaff')->name('add.staff');
        Route::post('/personel-gonder', 'StoreStaff')->name('store.staff');
        Route::get('/personel/duzenle/{id}', 'EditStaff')->name('edit.staff');
        Route::post('/personel/guncelle/{id}', 'UpdateStaff')->name('update.personel');
        Route::get('/personel/sil/{id}', 'DeleteStaff')->name('delete.personel');

        Route::post('/check-username', 'checkUsernameAvailability')->name('check.username.availability');

        //Dealer Routes
        Route::get('/bayiler', 'AllDealers')->name('dealers')->middleware(['permission:Bayileri Görebilir']);
        Route::get('/bayi-ekle', 'AddDealer')->name('add.dealer')->middleware('check.storage');
        Route::post('/bayi-kaydet', 'StoreDealer')->name('store.dealer');
        Route::get('/bayi/duzenle/{id}', 'EditDealer')->name('edit.dealer');
        Route::post('/bayi/guncelle/{id}', 'UpdateDealer')->name('update.dealer');
        Route::get('/bayi-sil/{id}', 'DeleteDealer')->name('delete.dealer');
        Route::get('/bayiler/data', 'GetDealersData')->name('dealers.data');
        Route::get('/dealer-document/{user_id}/{index?}','ShowDealerDocument')->name('dealer.document');
    });
    
    Route::controller(StockController::class)->group(function() {
        Route::get('/stoklar', 'AllStocks')->name('stocks');
        Route::get('/stoklar/data', 'GetStocksAjax')->name('stocks.ajax');
        Route::get('/stok-ekle', 'AddStock')->name('add.stock');
        Route::post('/stok-kaydet', 'StoreStock')->name('store.stock'); 
        Route::get('/stok/duzenle/{id}', 'EditStock')->name('edit.stock');
        Route::post('/stok/guncelle/{id}', 'UpdateStock')->name('update.stock');
        Route::get('/stok-sil/{id}', 'DeleteStock')->name('delete.stock');

        //Arama İnputları
        Route::get('/search-suppliers','searchSuppliers')->name('search.suppliers');
        Route::get('/search-personnel',  'searchPersonnel')->name('search.personnel');
        Route::get('/search-brands','searchBrands')->name('search.brands');
        Route::get('/search-devices', 'searchDevices')->name('search.devices');
        Route::get('/search-categories',  'searchCategories')->name('search.categories');
        Route::get('/search-shelves', 'searchShelves')->name('search.shelves');
        //Marka, kategori,cihaz tüür ve raf ekleme 
        Route::post('/stock/add-brand-ajax', 'storeBrandAjax')->name('store.brand.ajax');
        Route::post('/stock/add-device-type-ajax', 'storeDeviceTypeAjax')->name('store.device.type.ajax');
        Route::post('/store-service-resource-ajax', 'storeServiceResourceAjax')->name('store.service.resource.ajax');
        Route::post('/stock/add-category-ajax',  'storeCategoryAjax')->name('store.category.ajax');
        Route::post('/stock/add-shelf-ajax','storeShelfAjax')->name('store.shelf.ajax');
        //Stok haraketeri
        Route::get('/stok-haraketleri/{id}', 'StokActions')->name('stock.actions');
        Route::post('/stok-haraket-kaydet', 'StoreStockAction')->name('store.stock.action');
        Route::delete('/stok-haraket-sil/{id}', 'DeleteStockAction')->name('delete.stock.action');

        //Personel Haraketleri
        Route::get('/personel-stoklari/{id}', 'GetPersonelStocks')->name('stock.personel');

        //Stok Fotoğrafları
        Route::get('/stok-fotograflar/{stock_id}','getPhotos')->name('stock.photos');
        Route::post('/stok-foto-ekle','uploadPhoto')->name('stock.photos.update')->middleware('check.storage');
        Route::post('/stok-foto-sil', 'deletePhoto')->name('stock.photos.delete');

        //Stok Barkod
        Route::get('/stok-barkod/{id}', 'barkodPdf')->name('stok.barkod.pdf');

        //Ürün Adı Kontrol
        Route::post('/stok/urun-adi-kontrol', 'checkProductName')->name('stok.urunadi.kontrol');

        //Konsinye Cihazlar
        Route::get('/konsinye-cihazlar', 'consignmentDevice')->name('consignmentdevice');
        Route::get('/konsinye-cihazlar/data', 'consignmentDeviceData')->name('consignmentdevice.data');
        Route::get('/konsinye-cihaz-ekle', 'AddConsignmentDevice')->name('add.consignment.device');
        Route::post('/konsinye-cihaz-kaydet', 'StoreConsignmentDevice')->name('store.consignment.device');

        // Konsinye cihaz düzenleme ve güncelleme
        Route::get('/konsinye-cihazlar/duzenle/{id}', 'EditConsignmentDevice')->name('edit.consignment.device');
        Route::post('/konsinye-cihazlar/guncelle/{id}', 'UpdateConsignmentDevice')->name('update.consignment.device');

        // Konsinye cihaz stok hareketleri, personel stokları, fotoğraflar için ajax çağrıları
        Route::get('/stok-konsinye-hareketleri/{id}', 'ConsignmentStockActions')->name('consignment.stock.actions');
        Route::post('/stok-konsinye-hareket-kaydet', 'StoreConsignmentStockAction')->name('store.consignment.stock.action');
        Route::delete('/stok-konsinye-hareket-sil/{id}', 'DeleteConsignmentStockAction')->name('delete.consignment.stock.action');
        Route::get('/stok-konsinye-fotograflar/{stock_id}', 'GetConsignmentPhotos')->name('consignment.stock.photos');
        Route::post('/stok-konsinye-foto-ekle', 'UploadConsignmentPhoto')->name('consignment.stock.photos.update')->middleware('check.storage');
        Route::post('/stok-konsinye-foto-sil', 'DeleteConsignmentPhoto')->name('consignment.stock.photos.delete');

        //Konsinye Cihaz Barkod
        Route::get('/konsinye-cihaz-barkod/{id}', 'ConsignmentBarcode')->name('consignment.device.barcode.pdf');
    });

    Route::controller(CustomerController::class)->group(function() {
        Route::get('/musteriler', 'AllCustomer')->name('customers');
        Route::get('/musteri-ekle', 'AddCustomer')->name('add.customer');
        Route::post('/musteri-gonder', 'StoreCustomer')->name('store.customer');
        Route::get('/musteri/duzenle/{id}', 'EditCustomer')->name('edit.customer');
        Route::post('/musteri/guncelle/{id}', 'UpdateCustomer')->name('update.customer');
        Route::get('/musteri/sil/{id}', 'DeleteCustomer')->name('delete.customer');
        Route::get('/musteri-servisleri/{id}', 'CustomerServices')->name('customer.services');
    });

    Route::controller(OfferController::class)->group(function() {
        Route::get('/teklifler', 'AllOffer')->name('offers');
        Route::get('/teklif-ekle', 'AddOffer')->name('add.offer');
        Route::post('/teklif-kaydet', 'StoreOffer')->name('store.offer');
        Route::get('/teklif-duzenle/{id}', 'EditOffer')->name('edit.offer');
        Route::post('/teklif-guncelle', 'UpdateOffer')->name('update.offer');
        Route::get('/teklif-sil/{id}', 'DeleteOffer')->name('delete.offer');
        Route::get('/teklif-yazdir/{id}', 'OffertoPdf')->name('offer.pdf');
        Route::post('/offers/search-customer',  'searchMusteri') ->name('search.customer');
    });

    //GENEL AYARLAR MODÜLÜ
    Route::controller(GenelAyarlarController::class)->group(function() {
        Route::get('/genel-ayarlar', 'GeneralSettings')->name('general.settings')->middleware(['permission:Genel Ayarları Görebilir']);
        Route::get('/firma-bilgileri', 'CompanySettings')->name('firma.settings');
        Route::post('/firma-ayari/guncelle', 'UpdateCompanySet')->name('update.firma');
        Route::get('/sms-ayarlari', 'SmsSettings')->name('sms.settings');
        Route::post('/sms-ayari/guncelle', 'UpdateSms')->name('update.sms');
        Route::get('/prim-ayarlari', 'PrimSettings')->name('prim.settings');
        Route::post('/prim-ayari/guncelle', 'UpdateFirmPrim')->name('update.firm.prim');
    });

    Route::controller(DeviceBrandsController::class)->group(function() {
        Route::get('/cihaz-markalari', 'DeviceBrands')->name('device.brands');
        Route::get('/cihaz-ekle', 'AddDevice')->name('add.device');
        Route::post('/cihaz-yukle', 'StoreDevice')->name('store.device');
        Route::get('/cihaz-duzenle/{id}', 'EditDevice')->name('edit.device');
        Route::post('/cihaz-guncelle', 'UpdateDevice')->name('update.device');
        Route::delete('/cihaz-sil/{id}', 'DeleteDevice')->name('delete.device');
    });

    Route::controller(DeviceTypesController::class)->group(function() {
        Route::get('/cihaz-turleri', 'DeviceTypes')->name('device.types');
        Route::get('/cihaz-turu/ekle', 'AddDeviceType')->name('add.device.type');
        Route::post('/cihaz-turu/yukle', 'StoreDeviceType')->name('store.device.type');
        Route::get('/cihaz-turu/duzenle/{id}', 'EditDeviceType')->name('edit.device.type');
        Route::post('/cihaz-turu/guncelle', 'UpdateDeviceType')->name('update.device.type');
        Route::delete('/cihaz-turu/sil/{id}', 'DeleteDeviceType')->name('delete.device.type');
    });

    Route::controller(WarrantyPeriodController::class)->group(function() {
        Route::get('/garanti-sureleri', 'WarrantyPeriods')->name('warranty.period');
        Route::get('/garanti-ekle', 'AddWarrantyPeriod')->name('add.warranty');
        Route::post('/garanti-yukle', 'StoreWarrantyPeriod')->name('store.warranty');
        Route::get('/garanti-duzenle/{id}', 'EditWarrantyPeriod')->name('edit.warranty');
        Route::post('/garanti-guncelle', 'UpdateWarrantyPeriod')->name('update.warranty');
        Route::delete('/garanti-sil/{id}', 'DeleteWarrantyPeriod')->name('delete.warranty');
    });

    Route::controller(CarController::class)->group(function() {
        Route::get('/araclar', 'AllCars')->name('all.cars');
        Route::get('/arac-ekle', 'AddCar')->name('add.car');
        Route::post('/arac-yukle', 'StoreCar')->name('store.car');
        Route::get('/arac-duzenle/{id}', 'EditCar')->name('edit.car');
        Route::post('/arac-guncelle', 'UpdateCar')->name('update.car');
        Route::delete('/arac-sil/{id}', 'DeleteCar')->name('delete.car');
    });

    Route::controller(ServiceStagesController::class)->group(function() {
        Route::get('/servis-asamalari', 'AllServiceStage')->name('service.stages');
        Route::get('/servis-asama/ekle', 'AddServiceStage')->name('add.service.stage');
        Route::post('/servis-asama/yukle', 'StoreServiceStage')->name('store.service.stage');
        Route::get('/servis-asama/duzenle/{id}', 'EditServiceStage')->name('edit.service.stage');
        Route::post('/servis-asama/guncelle', 'UpdateServiceStage')->name('update.service.stage');
        Route::delete('/servis-asama/sil/{id}', 'DeleteServiceStage')->name('delete.service.stage');
    });

    Route::controller(ServiceTimeController::class)->group(function() {
        Route::get('/servis-zamanlama', 'ServiceTime')->name('service.time');
        Route::post('/servis-zamani/yukle', 'UpdateServiceTime')->name('update.service.time');
    });

    Route::controller(ServiceResourceController::class)->group(function() {
        Route::get('/servis-kaynaklari', 'AllServiceResource')->name('service.resources');
        Route::get('/servis-kaynak/ekle', 'AddServiceResource')->name('add.service.resource');
        Route::post('/servis-kaynak/yukle', 'StoreServiceResource')->name('store.service.resource');
        Route::get('/servis-kaynak/duzenle/{id}', 'EditServiceResource')->name('edit.service.resource');
        Route::post('/servis-kaynak/guncelle', 'UpdateServiceResource')->name('update.service.resource');
        Route::delete('/servis-kaynak/sil/{id}', 'DeleteServiceResource')->name('delete.service.resource');
    });

    Route::controller(StageQuestionController::class)->group(function() {
        Route::get('/servis-asama-sorulari', 'AllStageQuestions')->name('all.stage.questions');
        Route::get('/servis-asama-sorusu/ekle', 'AddStageQuestion')->name('add.stage.question');
        Route::post('/servis-asama-sorusu/yukle', 'StoreStageQuestion')->name('store.stage.question');
        Route::get('/servis-asama-sorusu/duzenle/{id}', 'EditStageQuestion')->name('edit.stage.question');
        Route::post('/servis-asama-sorusu/guncelle', 'UpdateStageQuestion')->name('update.stage.question');
        Route::delete('/servis-asama-sorusu/sil/{id}', 'DeleteStageQuestion')->name('delete.stage.question');
        
        Route::get('/stage-questions/get', 'getStageQuestions')->name('get.stage.questions');
    });

    
    Route::controller(RoleController::class)->group(function () {
        Route::get('/izinler', 'AllPermission')->name('all.permission');
        Route::get('/izin/ekle', 'AddPermission')->name('add.permission');
        Route::post('/izin/gonder', 'StorePermission')->name('store.permission');
        Route::get('/izin/duzenle/{id}', 'EditPermission')->name('edit.permission');
        Route::post('/izin/guncelle', 'UpdatePermission')->name('update.permission');
        Route::delete('/izin/sil/{id}', 'DeletePermission')->name('delete.permission');
    });
    
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roller', 'AllRoles')->name('all.roles');
        Route::get('/rol/ekle', 'AddRoles')->name('add.roles');
        Route::post('/rol/gonder', 'StoreRoles')->name('store.roles');
        Route::get('/rol/duzenle/{id}', 'EditRoles')->name('edit.roles');
        Route::post('/rol/guncelle', 'UpdateRoles')->name('update.roles');
        Route::delete('/rol/sil/{id}', 'DeleteRoles')->name('delete.roles');


        Route::get('/rollere/izin/ekle', 'AddRolesPermission')->name('add.roles.permission');
        Route::post('/rollere/izin/kaydet', 'StoreRolesPermission')->name('store.roles.permission');
        Route::get('/rollerdeki/izinler', 'AllRolesPermission')->name('all.roles.permission');
        Route::get('/rollerdeki/izinleri/duzenle/{id}', 'EditRolesPermission')->name('edit.roles.permission');
        Route::post('/rollerdeki/izinleri/guncelle/{id}', 'UpdateRolesPermission')->name('update.roles.permission');
        Route::get('/rollerdeki/izinleri/sil/{id}', 'DeleteRolesPermission')->name('delete.roles.permission');
    });


    Route::controller(StockCategoryController::class)->group(function() {
        Route::get('/stok-kategorileri', 'AllStockCategory')->name('stock.categories');
        Route::get('/stok-kategori/ekle', 'AddStockCategory')->name('add.stock.category');
        Route::post('/stok-kategori/yukle', 'StoreStockCategory')->name('store.stock.category');
        Route::get('/stok-kategori/duzenle/{id}', 'EditStockCategory')->name('edit.stock.category');
        Route::post('/stok-kategori/guncelle', 'UpdateStockCategory')->name('update.stock.category');
        Route::delete('/stok-kategori/sil/{id}', 'DeleteStockCategory')->name('delete.stock.category');
    });

    Route::controller(StockShelfController::class)->group(function() {
        Route::get('/stok-raflari', 'AllStockShelf')->name('stock.shelves');
        Route::get('/stok-raf/ekle', 'AddStockShelf')->name('add.stock.shelf');
        Route::post('/stok-raf/yukle', 'StoreStockShelf')->name('store.stock.shelf');
        Route::get('/stok-raf/duzenle/{id}', 'EditStockShelf')->name('edit.stock.shelf');
        Route::post('/stok-raf/guncelle', 'UpdateStockShelf')->name('update.stock.shelf');
        Route::delete('/stok-raf/sil/{id}', 'DeleteStockShelf')->name('delete.stock.shelf');
    });

    Route::controller(StockSupplierController::class)->group(function() {
        Route::get('/stok-tedarikcileri', 'AllStockSupplier')->name('stock.suppliers');
        Route::get('/stok-tedarikci/ekle', 'AddStockSupplier')->name('add.stock.supplier');
        Route::post('/stok-tedarikci/yukle', 'StoreStockSupplier')->name('store.stock.supplier');
        Route::get('/stok-tedarikci/duzenle/{id}', 'EditStockSupplier')->name('edit.stock.supplier');
        Route::post('/stok-tedarikci/guncelle', 'UpdateStockSupplier')->name('update.stock.supplier');
        Route::delete('/stok-tedarikci/sil/{id}', 'DeleteStockSupplier')->name('delete.stock.supplier');
    });


    Route::controller(PaymentMethodsController::class)->group(function() {
        Route::get('/odeme-sekilleri', 'AllPaymentMethods')->name('payment.methods');
        Route::get('/odeme-sekli/ekle', 'AddPaymentMethod')->name('add.payment.method');
        Route::post('/odeme-sekli/yukle', 'StorePaymentMethod')->name('store.payment.method');
        Route::get('/odeme-sekli/duzenle/{id}', 'EditPaymentMethod')->name('edit.payment.method');
        Route::post('/odeme-sekli/guncelle', 'UpdatePaymentMethod')->name('update.payment.method');
        Route::delete('/odeme-sekli/sil/{id}', 'DeletePaymentMethod')->name('delete.payment.method');
    });

    Route::controller(PaymentTypesController::class)->group(function() {
        Route::get('/odeme-turleri', 'AllPaymentTypes')->name('payment.types');
        Route::get('/odeme-turu/ekle', 'AddPaymentType')->name('add.payment.type');
        Route::post('/odeme-turu/yukle', 'StorePaymentType')->name('store.payment.type');
        Route::get('/odeme-turu/duzenle/{id}', 'EditPaymentType')->name('edit.payment.type');
        Route::post('/odeme-turu/guncelle', 'UpdatePaymentType')->name('update.payment.type');
        Route::delete('/odeme-turu/sil/{id}', 'DeletePaymentType')->name('delete.payment.type');
    });

    Route::controller(ServiceFormSetController::class)->group(function() {
        Route::get('/servis-form/ayarlari', 'ServiceFormSettings')->name('service.form.settings');
        Route::post('/servis-form/guncelle', 'UpdateServiceFormSettings')->name('update.service.form.settings');
    });

    Route::controller(ReceiptDesignController::class)->group(function() {
        Route::get('/yazici-fis/tasarimi', 'ReceiptDesign')->name('receipt.design');
        Route::post('/fis-tasarimi/guncelle', 'UpdateReceiptDesign')->name('update.receipt.design');
    });
    Route::controller(LegalContentController::class)->group(function() {
        Route::get('/yasal-metinler', 'legalSettings')->name('legal.settings');
        Route::post('/yasal-metinler/guncelle', 'updateLegalSettings')->name('update.legal.settings');
        
        // Popup için API endpoints
        Route::get('/api/kullanim-kosullari', 'getTermsContent')->name('api.terms');
        Route::get('/api/gizlilik-politikasi', 'getPrivacyContent')->name('api.privacy');
    });

    //SERVİSLER MODÜLÜ
    Route::controller(ServicesController::class)->group(function() {
        Route::get('/servisler', 'AllServices')->name('all.services');
        Route::get('/servis/ekle', 'AddService')->name('add.service');
        Route::post('/servis/yukle', 'StoreService')->name('store.service');
        Route::get('/servis/duzenle/{id}', 'EditService')->name('edit.service');
        Route::post('/servis/guncelle', 'UpdateService')->name('update.service');
        Route::get('/servis/sil/{id}', 'DeleteService')->name('delete.service');

        Route::post('/customer/search', 'searchCustomer')->name('customer.search');

        Route::get('/servis-bilgileri/tum/{id}', 'TumServiceDesc')->name('tum.service.desc');
        Route::get('/servis-bilgileri/kendi/{id}', 'KendiServiceDesc')->name('kendi.service.desc');
        Route::get('/teknisyen-depo/{personel_id}', 'StaffStocks')->name('staff.stocks');
        
        
        Route::get('/servis-musteri/duzenle/{id}', 'EditServiceCustomer')->name('edit.service.customer');
        Route::get('/servis-asama-sorusu-getir/{asamaid}/{serviceid}', 'ServiceStageQuestionShow')->name('service.stage.question.show');
        Route::post('/servis-plan-kaydet', 'SaveServicePlan')->name('save.service.plan');
        Route::get('/servis-asama/{id}/history', 'getServiceStageHistory')->name('service.stage.history');
        Route::post('/servis-plan-sil/{planid}', 'DeleteServicePlan')->name('delete.service.plan');
        Route::get('/servis-plan/duzenle/{planid}', 'EditServicePlan')->name('edit.service.plan');
        Route::post('/servis-plan/guncelle', 'UpdateServicePlan')->name('update.service.plan');
        
        //servis yazdırma
        Route::get('/servis-yazdir/{id}', 'ServicetoPdf')->name('serviceto.pdf');

        //Fiş Kopyalama
        Route::get('/servis/{servis_id}/fis-icerigi', 'getFisIcerigi')->name('servis.fis-icerigi');

        //servis para hareketleri
        Route::get('/servis-para-hareketleri/{service_id}', 'ServiceMoneyActions')->name('service.money.actions');
        Route::get('/servis-gelir-ekle/{service_id}', 'AddServiceIncome')->name('add.service.income');
        Route::post('/servis-gelir-kaydet', 'StoreServiceIncome')->name('store.service.income');
        Route::get('/servis-gider-ekle/{service_id}', 'AddServiceExpense')->name('add.service.expense');
        Route::post('/servis-gider-kaydet', 'StoreServiceExpense')->name('store.service.expense');
        Route::get('/servis-para-hareketi/duzenle/{payment_id}', 'EditServiceMoneyAction')->name('edit.service.money.action');
        Route::post('/servis-para-hareketi/guncelle', 'UpdateServiceMoneyAction')->name('update.service.money.action');
        Route::delete('/servis-para-hareketi/sil/{payment_id}', 'DeleteServiceMoneyAction')->name('delete.service.money.action');
    
        //Servis Fotoğrafları kısmı
        Route::get('/servis-fotolari/{service_id}', 'ServicePhotos')->name('service.photos');
        Route::post('/servis-foto-yukle', 'StoreServicePhoto')->name('store.service.photo')->middleware('check.storage');
        Route::delete('/servis-foto-sil/{fotoid}', 'DeleteServicePhoto')->name('delete.service.photo');
        
        //Servis Fiş Notları Bölümü
        Route::get('/servis-fis-notlari/{service_id}', 'ServiceReceiptNotes')->name('service.receipt.notes');
        Route::get('/servis-fis-notu/ekle/{service_id}', 'AddServiceReceiptNote')->name('add.service.receipt.note');
        Route::post('/servis-fis-notu/kaydet', 'StoreReceiptNote')->name('store.receipt.note');
        Route::get('/servis-fis-notu/duzenle/{note_id}', 'EditServiceReceiptNote')->name('edit.service.receipt.note');
        Route::post('/servis-fis-notu/guncelle', 'UpdateServiceReceiptNote')->name('update.service.receipt.note');
        Route::delete('/servis-fis-notu/sil/{note_id}', 'DeleteReceiptNote')->name('delete.receipt.note');
        
        //Servis Operatör Notları Bölümü
        Route::get('/servis-operator-notlari/{service_id}', 'ServiceOptNotes')->name('service.opt.notes');
        Route::get('/servis-opt-notu/ekle/{service_id}', 'AddServiceOptNote')->name('add.service.opt.note');
        Route::post('/servis-opt-notu/kaydet', 'StoreServiceOptNote')->name('store.service.opt.note');
        Route::get('/servis-opt-notu/duzenle/{note_id}', 'EditServiceOptNote')->name('edit.service.opt.note');
        Route::post('/servis-opt-notu/guncelle', 'UpdateServiceOptNote')->name('update.service.opt.note');
        Route::delete('/servis-opt-notu/sil/{note_id}', 'DeleteServiceOptNote')->name('delete.service.opt.note');
        
        //Servisler modalında teklifler Bölümü
        Route::get('/musteri-teklifleri/{service_id}', 'CustomerOffers')->name('customer.offers');
        
        //Servisler modalında faturalar Bölümü
        Route::get('/musteri-faturalari/{service_id}', 'CustomerInvoices')->name('customer.invoices');

        //Servisler modalı kosinye cihaz bilgisi güncelleme
        Route::get('/servis-konsinye-cihaz/{service_id}','getServicesKonsinyeCihaz');
        
    });

    Route::controller(ServiceReportsController::class)->group(function() { 
        //Servisler sayfasında sağ üstteki raporlar butonuna tıklanınca açılan modal route u
        Route::get('/servis-rapor-modal', 'ServiceReports')->name('service.reports');
    });

    //TOPLU SERVİS PLANLAMA
    Route::controller(ServiceBatchPlanningController::class)->group(function() { 
        //Servisler sayfasında sol üstteki servis planlama butonuna tıklanınca açılan modal route u
        Route::get('/servis-toplu-planlama/', 'ServiceBatchPlanning')->name('service.batch.planning');
        Route::get('/ilce-getir', 'getDistricts')->name('service.districts');
        Route::get('/servis-liste-getir/','getServiceList')->name('service.list');
        Route::get('/servis-atama-formu','getServicePlanForm')->name('service.plan.form');
        Route::post('/servis-atama', 'assignService')->name('service.assign');
        Route::get('/servis-atama-guncelle-formu/','getServicePlanUpdateForm')->name('service.plan.update.form');
        Route::post('/servis-personel-atama-guncelleme', 'updatePersonelBatch')->name('update.personel.batch');
    });

    //GELEN ÇAĞRILAR MODÜLÜ
    Route::controller(IncomingCallsController::class)->group(function() { 
        //Servisler sayfasında sol üstteki gelen çağrılar butonuna tıklanınca açılan modal route u
        Route::get('/gelen-cagrilar-datatable', 'gelenCagrilarDatatable')->name('gelen-cagrilar.datatable');
        Route::get('/yeni-cagri-ekle', 'AddCall')->name('add.call');
        Route::post('/yeni-cagri-gonder', 'StoreCall')->name('store.call');
        Route::get('/yeni-cagri-duzenle/{call_id}', 'EditCall')->name('edit.call');
        Route::post('/yeni-cagri-guncelle', 'UpdateCall')->name('update.call');
        Route::delete('/yeni-cagri-sil/{call_id}', 'DeleteCall')->name('delete.call');

        //markalar seçildiğinde ona ait servis numarasını getiren fonksiyon
        Route::post('/get-brand-phone', 'getPhone')->name('get.brand.phone');

        //çağrı kaydederken önceki arızadan seçebilmeyi sağlayan route
        Route::get('/ariza/search', 'arizaSearch')->name('ariza.search');
    });

    //SİLİNEN SERVİSLER MODÜLÜ
    Route::controller(DeletedServicesController::class)->group(function() { 
        //Silinen servisler sayfası
        Route::get('/silinen-servisler', 'DeletedServices')->name('deleted.services');
        Route::post('/servis-geri-al/{service_id}', 'RestoreService')->name('restore.service');
    });

    Route::controller(CashTransactionsController::class)->group(function () {
        Route::get('/kasa-filtrele', 'Filter')->name('kasa.filter');
        Route::get('/kasa-hareketi/ekle', 'AddCashTransaction')->name('add.cash.transaction');
        Route::post('/kasa-hareketi/gonder', 'StoreCashTransaction')->name('store.cash.transaction');
        Route::get('/kasa-hareketi/duzenle/{id}', 'EditCashTransaction')->name('edit.cash.transaction');
        Route::post('/kasa-hareketi/guncelle', 'UpdateCashTransaction')->name('update.cash.transaction');
        Route::get('/kasa-hareketi/sil/{id}', 'DeleteCashTransaction')->name('delete.cash.transaction');

        Route::get('/kasa-odeme/getir/{id}', 'GetCashPayment')->name('get.cash.payment');
        Route::get('/kasa-toplam', 'updateTotalValues');
        Route::post('/musteri-ara-kasa', 'searchMusteri')->name('search.customer.kasa');
    });

    Route::controller(InvoicesController::class)->group(function () {
        Route::get('/faturalar', 'AllInvoice')->name('all.invoices');
        Route::get('/fatura/ekle', 'AddInvoice')->name('add.invoices');
        Route::post('/fatura/gonder', 'StoreInvoice')->name('store.invoices')->middleware('check.storage');
        Route::get('/fatura/duzenle/{id}', 'EditInvoice')->name('edit.invoices');
        Route::post('/fatura/guncelle', 'UpdateInvoice')->name('update.invoices');
        Route::get('/fatura/sil/{id}', 'DeleteInvoice')->name('delete.invoices');

        Route::get('/fatura/goruntule/{id}', 'ShowInvoice')->name('show.invoice');
        Route::post('/fatura/yukle', 'UploadInvoice')->name('upload.invoices')->middleware('check.storage');
        Route::post('/eArsiv/sil/{id}', 'DeleteEinvoice')->name('delete.einvoice');

        Route::get('/fatura-sonuc', 'GetInvoices');
        Route::post('/fatura/musteri-getir', 'musteriGetir')->name('fatura.musteri.getir');
        Route::post('/musteri-ara-fatura','searchMusteri')->name('search.customer.invoice');

        Route::post('/fatura/tahsilat-ekle', 'addPaymentToInvoice')->name('invoice.add.payment');
        Route::get('/parasut/hesaplar', 'getParasutAccounts')->name('parasut.accounts');
        Route::get('/fatura/{invoice_id}/odemeler', 'getInvoicePayments')->name('invoice.payments');
        Route::delete('/fatura/tahsilat-sil', 'deletePaymentFromInvoice')->name('delete.invoice.payment');

        Route::post('/fatura/resmilestirir', 'formalizeInvoice')->name('formalize.invoice');
        Route::get('/fatura/{invoice_id}/resmilestirme-durumu', 'checkFormalizationStatusController')->name('check.formalization.status');
        // Route::get('/fatura-baglanti', 'testIntegration');
    });

    Route::controller(PrimController::class)->group(function() { 
        Route::get('/prim', 'index')->name('index');
    
        // Prim hesaplama işlemi (AJAX)
        Route::post('/hesapla', 'hesapla')->name('prim.hesapla');
        
        // Günlük prim detayı (AJAX)
        Route::get('/detay', 'detay')->name('prim.detay');
        
        Route::get('/primlerim', 'kullaniciPrimSayfasi')->name('prim.kullanici');
        Route::post('/primlerim/hesapla', 'kullaniciHesapla')->name('prim.kullanici.hesapla');
        Route::get('/primlerim/detay', 'kullaniciDetay')->name('prim.kullanici.detay');
        
    });

    Route::prefix('payment-history')->name('payment-history.')->group(function () {
        Route::get('/', [PaymentHistoryController::class, 'index'])
            ->name('index')->middleware(['permission:Ödeme Geçmişini Görebilir']);
        Route::get('/export', [PaymentHistoryController::class, 'export'])
            ->name('export');  
        Route::get('/invoice/{type}/{id}', [PaymentHistoryController::class, 'downloadInvoice'])
            ->name('invoice')
            ->where(['type' => '(subscription|storage)', 'id' => '[0-9]+']);
    });

    // Route::controller(TenantsController::class)->group(function() { 
    //     //Admin rollü kişiye gözükecek firmalar modülü routeları
    //     Route::get('/firmalar', 'AllTenants')->name('all.tenants');
    //     Route::get('/firma-duzenle/{firma_id}', 'EditTenant')->name('edit.tenant');
    //     Route::post('/firma-guncelle', 'UpdateTenant')->name('update.tenant');
    //     Route::get('/firma-sil/{id}', 'DeleteTenant')->name('delete.tenant');

    //     // Bu rotayı diğer tenant rotalarınızın yanına ekleyebilirsiniz.
    //     Route::post('/tenants/change-status/{id}', 'changeTenantStatus')->name('tenant.changeStatus');

    // });

        Route::controller(IntegrationMarketplaceController::class)->group(function() { 
            Route::get('/entegrasyonlar', 'index')->name('tenant.integrations.marketplace');
            Route::get('/entegrasyon-detay/{slug}', 'show')->name('tenant.integrations.show');
            
            //detaylar sayfasındaki api ayarlarını girerek veritabanına kaydeden urll
            Route::post('/entegrasyon/{integration_id}/ayarlar-kaydet', 'saveSettings')->name('tenant.integrations.save_settings');

             // Satın Alma
            Route::get('/entegrasyon/{integration_id}/satin-al', 'purchase')->name('tenant.integrations.purchase');
       
            // Hipcall çağrıları sayfası
            Route::get('/integrations/hipcall/calls','showHipcallCalls')
                ->name('tenant.integrations.hipcall.calls');
            
            // AJAX - Çağrıları çek
            Route::post('/integrations/hipcall/fetch-calls', 'fetchHipcallCalls')
                ->name('tenant.integrations.hipcall.fetch-calls');

            
        
        });

        Route::controller(VerimorSantralController::class)
        ->prefix('integrations/verimor-santral')
        ->name('tenant.integrations.verimor-santral.')
        ->group(function() {
            // Web telefonu ana sayfa
            Route::get('/webphone', 'showWebphone')->name('webphone');
            
            // AJAX - iframe HTML al
            Route::get('/get-iframe', 'getIframe')->name('get-iframe');
            
            // AJAX - Token yenile
            Route::post('/refresh-token', 'refreshToken')->name('refresh-token');
            
            // Bağlantı testi
            Route::get('/test-connection', 'testConnection')->name('test-connection');
        });

        //Toplu SMS modülü routeları
        Route::controller(BulkSmsController::class)->group(function() { 
            Route::get('/toplu-sms',  'index')->name('toplu-sms.index');
            Route::get('/toplu-sms/listele', 'list')->name('toplu-sms.listele');
            Route::post('/toplu-sms/gonder', 'send')->name('toplu-sms.gonder');
        });

      Route::controller(TenantApiTokenController::class)->group(function() {
        Route::get('/api-tokens', 'index')->name('api.tokens.index');
        Route::get('/api-token/olustur', 'create');
        Route::post('/api-token/kaydet', 'store');
        Route::post('/api-token/aktif-pasif', 'toggle');
        Route::delete('/api-token/sil', 'destroy');
        Route::get('/api-token/goster', 'show');
    });

});
Route::controller(IntegrationMarketplaceController::class)->group(function() { 
    Route::get('entegrasyon/odeme-basarili', 'paymentSuccess')->name('integration.payment.success');
    Route::get('entegrasyon/odeme-basarisiz', 'paymentFail')->name('integration.payment.fail');
});      

Route::group(['prefix' => '{tenant_id}', 'middleware' => ['auth','check.tenant.status']], function () {
    Route::get('/abonelik-paketleri', [SubscriptionController::class, 'subscriptionPlans'])->name('abonelikler');
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans')->middleware(['permission:Abonelik Planını Görebilir']);
    Route::get('/subscription/{planid}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscription/{planid}', [SubscriptionController::class, 'processSubscription'])->name('subscription.process');
    Route::get('/subscription/{planid}/payment', [SubscriptionController::class, 'payment'])->name('subscription.payment');
    
    // Paytr ödeme rotaları
    Route::get('/subscription/{planid}/payment/initiate', [SubscriptionController::class, 'initiatePayment'])->name('subscription.payment.initiate');
    Route::get('/subscription/upgrade/{planid}', [SubscriptionController::class, 'updateSubscription'])->name('subscription.upgrade');
    
    Route::post('/subscription/payment/check-status', [SubscriptionController::class, 'checkPaymentStatus'])->name('subscription.payment.check');
});

Route::get('/payment/success', [SubscriptionController::class, 'paymentSuccess'])
    ->name('subscription.payment.success');

Route::get('/payment/fail', [SubscriptionController::class, 'paymentFail'])
    ->name('subscription.payment.fail');
Route::post('/subscription/payment/callback', [SubscriptionController::class, 'paymentCallback'])->name('subscription.payment.callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware(['auth'])->group(function () {
    // Storage bilgilerini getir (middleware olmadan)
    Route::get('/{tenant_id}/depolama-alani/bilgisi', [GenelAyarlarController::class, 'getStorageInfo'])->name('depolama.bilgisi');
    Route::get('/{tenant_id}/depolama-alani/bilgisi-json', [GenelAyarlarController::class, 'getStorageInfoJson'])
         ->name('depolama.bilgisi.json');
    
    // Storage detayları (bu yeni eklediğimiz)
    Route::get('/{tenant_id}/storage/details', [GenelAyarlarController::class, 'getStorageDetails'])
         ->name('tenant.storage.details');
    
    // Storage temizleme
    Route::post('/{tenant_id}/storage/cleanup', [GenelAyarlarController::class, 'cleanupStorageFiles'])
          ->name('tenant.storage.cleanup');

});

Route::middleware(['auth'])->group(function () {
    Route::prefix('{tenant_id}')->group(function () {
        Route::get('/storage-paketleri', [StorageController::class, 'packages'])->name('storage.packages')->middleware(['permission:Storage Paketlerini Görebilir']);
        Route::post('/storage-satin-al', [StorageController::class, 'purchase'])->name('storage.purchase');

    });
});
Route::get('/storage-odeme-basarili', [StorageController::class, 'paymentSuccess'])->name('storage.payment.success');
Route::get('/storage-odeme-basarisiz', [StorageController::class, 'paymentFail'])->name('storage.payment.fail');


Route::get('/logs', function () {
        $logFiles = File::files(storage_path('logs'));
        $logs = [];

        // Log dosyalarını tarihe göre sırala (en yeniden eskiye)
        usort($logFiles, function ($a, $b) {
            return -strcmp($a->getFilename(), $b->getFilename());
        });

        // Sadece son 30 günlük logları göster (isteğe bağlı)
        $logFiles = array_slice($logFiles, 0, 30);

        foreach ($logFiles as $file) {
            $filename = $file->getFilename();
            $date = str_replace(['laravel-', '.log'], '', $filename);

            // Dosya içeriğini oku (büyük dosyalar için dikkatli olun)
            // Sadece ilk 100 satırı veya belirli bir boyutu okuyabilirsiniz
            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);
            $logs[$date] = array_slice($lines, 0, 200); // İlk 200 satırı al

            // Veya sadece dosya adlarını ve boyutlarını listele
            // $logs[$date] = [
            //     'filename' => $filename,
            //     'size' => File::size($file->getPathname()) . ' bytes',
            // ];
        }

        return view('frontend.secure.logs.index', compact('logs'));
    })->middleware('auth'); 


Route::controller(HakkimizdaController::class)->group(function() {
    Route::get('/about', 'About')->name('about');
});

Route::controller(ProductsController::class)->group(function() {
    Route::get('/usage/areas', 'index')->name('products');
    Route::get('/usage/areas/{slug}', 'UrunDetails' )->name('product.details');
    Route::get('/urun/{slug}', 'Products')->name('products.alt');
});

Route::controller(KatalogController::class)->group(function() {
    Route::get('/kataloglar', 'index')->name('katalogs');
});

Route::controller(FrontendContactController::class)->group(function() {
    Route::get('/contact', 'index')->name('contact');
    Route::post('/store/message', 'StoreMessage')->name('store.message');
});

Route::controller(FeatureController::class)->group(function() {
    Route::get('/features', 'Features')->name('features');
    Route::get('/features/{slug}', 'FeatureDetails' )->name('feature.details');

});



Route::get('/{tenant_id}/test-hipcall-card-data/{customer_id}', function($tenant_id, $customer_id) {
    $customer = DB::table('customers')
        ->where('firma_id', $tenant_id)
        ->where('id', $customer_id)
        ->first();
    
    if (!$customer) {
        return response()->json(['error' => 'Müşteri bulunamadı']);
    }
    
    $hipcallService = new \App\Services\HipcallService($tenant_id);
    $baseUrl = rtrim(config('app.url'), '/');
    
    $cardData = $hipcallService->prepareCustomerCard($customer, $tenant_id, $baseUrl);
    
    return response()->json([
        'card_data' => $cardData,
     
    ]);
})->name('test.hipcall.card');

