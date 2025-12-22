<?php

namespace App\Services\SmsProviders;

interface SmsProviderInterface
{
    public function sendBulkSms(array $phones, string $message): array;
    public function sendSingleSms(string $phone, string $message): array;
}