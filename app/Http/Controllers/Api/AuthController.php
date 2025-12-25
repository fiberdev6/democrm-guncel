<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{   
    // Token yenileme
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        $request->user()->currentAccessToken()->delete();
        
        $token = $user->createToken('mobile-app', ['mobile'])->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'Token yenilendi',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 1440 // dakika cinsinden (1 gün)
            ]
        ], 200);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'E-posta adresi gereklidir',
            'email.email' => 'Geçerli bir e-posta adresi giriniz',
            'password.required' => 'Şifre gereklidir',
            'password.min' => 'Şifre en az 6 karakter olmalıdır',
        ]);

        $user = User::where('eposta', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'E-posta adresi veya şifre hatalı'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'E-posta adresi veya şifre hatalı'
            ], 401);
        }

        if ($user->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Hesabınız aktif değil. Lütfen yönetici ile iletişime geçin.'
            ], 403);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bilgisi bulunamadı'
            ], 404);
        }

        if ($tenant->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Firma hesabı aktif değil'
            ], 403);
        }

        if (!$this->checkSubscription($tenant)) {
            return response()->json([
                'success' => false,
                'message' => 'Firma aboneliği sona ermiş. Lütfen aboneliğinizi yenileyin.'
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('mobile-app', ['mobile'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'user' => [
                    'id' => $user->user_id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->eposta,
                    'tel' => $user->tel,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'firma_adi' => $tenant->firma_adi,
                    'logo' => $tenant->logo ? url($tenant->logo) : null,
                    'tel1' => $tenant->tel1,
                    'tel2' => $tenant->tel2,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 1440,
                'expires_at' => now()->addDay()->toISOString()
            ]
        ], 200);
    }

    // Abonelik kontrolü
    private function checkSubscription($tenant)
    {
        $now = now();

        if ($tenant->trial_ends_at && $now->lte($tenant->trial_ends_at)) {
            return true;
        }

        if ($tenant->subscription_status === 'active' && 
            $tenant->subscription_ends_at && 
            $now->lte($tenant->subscription_ends_at)) {
            return true;
        }

        return false;
    }


    public function me(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->user_id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->eposta,
                    'tel' => $user->tel,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'firma_adi' => $tenant->firma_adi,
                    'logo' => $tenant->logo ? url($tenant->logo) : null,
                ]
            ]
        ], 200);
    }
}
