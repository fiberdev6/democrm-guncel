<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

     protected function unauthenticated($request, AuthenticationException $exception)
    {
        // API istekleri için JSON döndür
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Token bulunamadı veya geçersiz. Lütfen giriş yapın.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Web istekleri için login sayfasına yönlendir
        return redirect()->guest(route('giris'));
    }
}
