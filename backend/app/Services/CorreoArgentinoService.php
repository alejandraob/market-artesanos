<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CorreoArgentinoService
{
    private $baseUrl;
    private $username;
    private $password;
    private $customerId;

    public function __construct()
    {
        $this->baseUrl = config('services.correo_argentino.sandbox') 
            ? 'https://apitest.correoargentino.com.ar/micorreo/v1/' 
            : 'https://api.correoargentino.com.ar/micorreo/v1/';
        
        $this->username = config('services.correo_argentino.username');
        $this->password = config('services.correo_argentino.password');
        $this->customerId = config('services.correo_argentino.customer_id');
    }

    private function getToken()
    {
        return Cache::remember('correo_argentino_token', now()->addHours(23), function () {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withOptions(['verify' => config('services.correo_argentino.verify_ssl', true)])
                ->post($this->baseUrl . 'token');

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Correo Argentino Login Failed', ['response' => $response->body()]);
            return null;
        });
    }

    public function getRates($destinationZip, $dimensions)
    {
        $token = $this->getToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->withOptions(['verify' => config('services.correo_argentino.verify_ssl', true)])
            ->post($this->baseUrl . 'rates', [
                'customerId' => $this->customerId,
                'postalCodeOrigin' => config('services.correo_argentino.origin_zip'),
                'postalCodeDestination' => $destinationZip,
                'deliveredType' => 'D', // Home delivery
                'dimensions' => $dimensions
            ]);

        if ($response->successful()) {
            return $response->json('rates');
        }

        Log::error('Correo Argentino Rates Failed', ['response' => $response->body()]);
        return null;
    }

    public function createOrder($orderData)
    {
        $token = $this->getToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->withOptions(['verify' => config('services.correo_argentino.verify_ssl', true)])
            ->post($this->baseUrl . 'shipping/import', array_merge([
                'customerId' => $this->customerId,
            ], $orderData));

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Correo Argentino Order Import Failed', ['response' => $response->body()]);
        return null;
    }
}
