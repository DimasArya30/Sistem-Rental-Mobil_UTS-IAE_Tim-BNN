<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class CarServiceClient
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('CAR_SERVICE_URL', 'http://localhost:8001');
    }

    /**
     * CONSUMER: Ambil data mobil dari CarService
     */
    public function getCar($carId)
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/api/cars/{$carId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data')
                ];
            }

            return [
                'success' => false,
                'message' => 'Car not found',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Car Service unavailable: ' . $e->getMessage()
            ];
        }
    }

    /**
     * CONSUMER: Update status mobil di CarService
     */
    public function updateCarStatus($carId, $status)
    {
        try {
            $response = Http::timeout(10)
                ->put("{$this->baseUrl}/api/cars/{$carId}/status", [
                    'status' => $status
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data')
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update car status'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Car Service unavailable: ' . $e->getMessage()
            ];
        }
    }
}
