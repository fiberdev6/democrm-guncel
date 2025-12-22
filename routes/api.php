<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\HipcallWebhookController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ImpersonationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

    

    
    Route::post('/webhook/hipcall/{token}', [HipcallWebhookController::class, 'handle'])
        ->name('hipcall.webhook.handle');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('impersonation')->group(function () {
        Route::post('/start/{user_id}', [ImpersonationController::class, 'start']);
        Route::post('/stop', [ImpersonationController::class, 'stop']);
        Route::get('/users/{tenant_id}', [ImpersonationController::class, 'getUsersForImpersonation']);
        Route::get('/history', [ImpersonationController::class, 'getImpersonationHistory']);
        Route::get('/status', [ImpersonationController::class, 'checkStatus']);
    });
});

// MOBİL UYGULAMA ENDPOİNTLERİ
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['check.token.expiration', 'auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);    
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    
    // Servis endpoint
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'myAssignedServices']); // Servis listesi
        Route::get('/{id}', [ServiceController::class, 'myAssignedServiceDetail']); // Servis detay
    });
    // Servis güncelleme endpointi
    Route::put('/update-service', [ServiceController::class, 'updateService']);

    // Personele atanan stoklar
    Route::get('/my-stocks', [ServiceController::class, 'myStocks']);
    
    //ilgili aşamaya ait aşama soru cevaplarını getiren endpoint
    Route::get('/stage-questions/{asama_id}', [ServiceController::class, 'getStageQuestions']);

    //servis plan kaydetme endpointi
    Route::post('/save-service-plan', [ServiceController::class, 'saveServicePlan']);
    Route::delete('/delete-service-plan/{plan_id}', [ServiceController::class, 'deleteServicePlan']);
    Route::get('/service-plan-update-form/{plan_id}', [ServiceController::class, 'getServicePlanUpdateForm']);
    Route::put('/update-service-plan', [ServiceController::class, 'updateServicePlan']);

    //İdsi girilen servisin fiş notlarını getiren endpoint
    Route::get('/service-receipt-notes/{servis_id}', [ServiceController::class, 'getServiceNotes']);
    Route::post('/add-service-note', [ServiceController::class, 'addServiceNote']);
    Route::delete('/delete-service-note/{note_id}', [ServiceController::class, 'deleteServiceNote']);

    //servis fotolarını getiren endpoint
    Route::get('/service-photos/{servis_id}', [ServiceController::class, 'getServicePhotos']);
    Route::post('/add-service-photo', [ServiceController::class, 'addServicePhoto']);
    Route::delete('/delete-service-photo/{photo_id}', [ServiceController::class, 'deleteServicePhoto']);

    //Ödeme şekillerini getiren endpoint
    Route::get('/payment-methods', [ServiceController::class, 'getPaymentMethods']);

    //Servis para hareketleri endpointi
    Route::get('/service-payments/{servis_id}', [ServiceController::class, 'getServicePayments']);
    Route::post('/add-service-income', [ServiceController::class, 'addServiceIncome']);
    Route::post('/add-service-expense', [ServiceController::class, 'addServiceExpense']);

    //Cihaz marka ve türleri endpointi
    Route::get('/device-brands', [ServiceController::class, 'getDeviceBrands']);
    Route::get('/device-types', [ServiceController::class, 'getDeviceTypes']);

    //Arıza kodları endpointleri
    Route::get('/brands', [ServiceController::class, 'getBrands']);
    Route::get('/models/{marka_id}', [ServiceController::class, 'getModels']);
    Route::get('/fault-codes/by-brand/{marka_id}', [ServiceController::class, 'getFaultCodesByBrand']);
    Route::get('/fault-codes/by-model/{marka_id}/{model_id}', [ServiceController::class, 'getFaultCodesByModel']);
    Route::get('/fault-codes/search', [ServiceController::class, 'getFaultCodesByCode']);

    //Kullanıcının primlerini hesaplayan endpoint
    Route::post('/calculate-my-bonus', [ServiceController::class, 'calculateMyBonus']);

    //Servis fişi yazdırma endpointi
    Route::get('/print-service-receipt/{servis_id}', [ServiceController::class, 'printServiceReceipt']);

});