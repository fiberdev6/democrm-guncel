<?php

return [
    'client_id' => env('PARASUT_CLIENT_ID'),
    'client_secret' => env('PARASUT_CLIENT_SECRET'),
    'username' => env('PARASUT_USERNAME'),
    'password' => env('PARASUT_PASSWORD'),
    'company_id' => env('PARASUT_COMPANY_ID'),
    'redirect_uri' => env('PARASUT_REDIRECT_URI', 'urn:ietf:wg:oauth:2.0:oob'),
    'api_url' => env('PARASUT_API_URL', 'https://api.parasut.com'),
];