<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CorreoArgentinoService
{
    private string $baseUrl;
    private string $apiKey;
    private string $agreement;
    private bool $verifySSL;

    public function __construct()
    {
        $this->baseUrl = config('services.correo_argentino.sandbox')
            ? 'https://apitest.correoargentino.com.ar/paqar/v1'
            : 'https://api.correoargentino.com.ar/paqar/v1';

        $this->apiKey = (string) config('services.correo_argentino.api_key', '');
        $this->agreement = (string) config('services.correo_argentino.agreement', '');
        $this->verifySSL = config('services.correo_argentino.verify_ssl', true);
    }

    private function http()
    {
        return Http::withHeaders([
                'Authorization' => 'Apikey ' . $this->apiKey,
                'Agreement' => $this->agreement,
                'Content-Type' => 'application/json',
            ])
            ->withOptions(['verify' => $this->verifySSL]);
    }

    /**
     * Validar credenciales - GET /v1/auth
     * Response: 204 OK, 400 Bad Request, 401 Unauthorized
     */
    public function authenticate(): bool
    {
        try {
            $response = $this->http()->get($this->baseUrl . '/auth');
            return $response->status() === 204;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Auth Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Alta de orden - POST /v1/orders
     */
    public function createOrder(array $orderData): ?array
    {
        try {
            $response = $this->http()->post($this->baseUrl . '/orders', $orderData);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Correo Argentino Create Order Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Create Order Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cancelar orden - PATCH /v1/orders/{trackingNumber}/cancel
     */
    public function cancelOrder(string $trackingNumber): bool
    {
        try {
            $response = $this->http()->patch($this->baseUrl . "/orders/{$trackingNumber}/cancel");
            return $response->status() === 200;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Cancel Order Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtener rótulo/etiqueta - POST /v1/labels
     * Envía array de trackingNumbers, devuelve PDF en base64
     */
    public function getLabel(string $trackingNumber): ?array
    {
        try {
            $response = $this->http()->post($this->baseUrl . '/labels', [
                ['trackingNumber' => $trackingNumber],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Correo Argentino Get Label Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Get Label Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Consultar historial de orden - GET /v1/orders/{trackingNumber}/history
     */
    public function getOrderHistory(string $trackingNumber): ?array
    {
        try {
            $response = $this->http()->get($this->baseUrl . "/orders/{$trackingNumber}/history");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Correo Argentino Get History Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Get History Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Consultar sucursales - GET /v1/agencies
     */
    public function getAgencies(bool $pickupAvailability = true): ?array
    {
        try {
            $response = $this->http()->get($this->baseUrl . '/agencies', [
                'pickup_availability' => $pickupAvailability,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Correo Argentino Get Agencies Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Get Agencies Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cotizar envío - POST /v1/rates
     */
    public function getRates(string $originZip, string $destinationZip, string $deliveryType, array $parcels, string $serviceType = 'CP'): ?array
    {
        try {
            $response = $this->http()->post($this->baseUrl . '/rates', [
                'agreement' => $this->agreement,
                'senderData' => ['zipCode' => $originZip],
                'shippingData' => ['zipCode' => $destinationZip],
                'deliveryType' => $deliveryType,
                'parcels' => $parcels,
                'serviceType' => $serviceType,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Correo Argentino Get Rates Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Correo Argentino Get Rates Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
