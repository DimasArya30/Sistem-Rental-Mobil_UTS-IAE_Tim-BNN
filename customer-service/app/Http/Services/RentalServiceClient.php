<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class RentalServiceClient
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('RENTAL_SERVICE_URL', 'http://localhost:8002');
    }

    /**
     * CONSUMER: Ambil riwayat rental customer dari RentalService
     */
    public function getCustomerRentals($customerId)
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/api/rentals/customer/{$customerId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data', [])
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch rental history',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Rental Service unavailable: ' . $e->getMessage()
            ];
        }
    }
}
