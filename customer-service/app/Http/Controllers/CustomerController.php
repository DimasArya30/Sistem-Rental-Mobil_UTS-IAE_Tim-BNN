<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Services\RentalServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    protected $rentalServiceClient;

    public function __construct(RentalServiceClient $rentalServiceClient)
    {
        $this->rentalServiceClient = $rentalServiceClient;
    }

    /**
     * PROVIDER: Get all customers
     * GET /api/customers
     */
    public function index()
    {
        $customers = Customer::all();

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], 200);
    }

    /**
     * PROVIDER: Get customer by ID
     * GET /api/customers/{id}
     */
    public function show($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $customer
        ], 200);
    }

    /**
     * PROVIDER: Create new customer
     * POST /api/customers
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:customers',
            'phone' => 'required|string|max:20',
            'ktp_number' => 'required|string|max:20',
            'address' => 'nullable|string'
        ]);

        $customer = Customer::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * PROVIDER: Hapus data customer (Delete Account)
     * DELETE /api/customers/{id}
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Keamanan: Cek apakah customer masih punya rental yang sedang aktif
        // CONSUMER: Memanggil RentalService
        try {
            $response = Http::timeout(5)->get(env('RENTAL_SERVICE_URL', 'http://localhost:8002') . '/api/rentals/customer/' . $id);

            if ($response->successful()) {
                $rentals = $response->json('data', []);

                // Cek jika ada rental yang statusnya masih 'active'
                foreach ($rentals as $rental) {
                    if (isset($rental['status']) && $rental['status'] === 'active') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal menghapus! Customer masih memiliki transaksi sewa yang sedang berjalan.',
                            'detail' => 'Kembalikan mobil terlebih dahulu sebelum menghapus akun.'
                        ], 422);
                    }
                }
            }
        } catch (\Exception $e) {
            // Kalau RentalService sedang mati, tetap lanjutkan hapus
        }

        // Jika aman, hapus customer
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus',
            'data' => [
                'id' => (int) $id,
                'name' => $customer->name,
                'email' => $customer->email
            ]
        ], 200);
    }

    /**
     * CONSUMER: Get rental history for a customer
     * Mengambil data dari RentalService
     * GET /api/customers/{id}/rentals
     */
    public function getRentalHistory($id)
    {
        // Cek apakah customer ada
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // CONSUMER: Call RentalService
        $rentals = $this->rentalServiceClient->getCustomerRentals($id);

        if (!$rentals['success']) {
            return response()->json([
                'success' => false,
                'message' => $rentals['message'],
                'customer' => $customer
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer with rental history retrieved',
            'customer' => $customer,
            'rental_history' => $rentals['data']
        ], 200);
    }
}
