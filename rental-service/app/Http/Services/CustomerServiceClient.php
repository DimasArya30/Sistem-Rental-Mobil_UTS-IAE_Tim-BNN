<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class CustomerServiceClient
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('CUSTOMER_SERVICE_URL', 'http://localhost:8080');
    }

    /**
     * CONSUMER: Ambil data customer dari CustomerService
     */
    public function getCustomer($customerId)
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/api/customers/{$customerId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data')
                ];
            }

            return [
                'success' => false,
                'message' => 'Customer not found',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Customer Service unavailable: ' . $e->getMessage()
            ];
        }
    }
}
