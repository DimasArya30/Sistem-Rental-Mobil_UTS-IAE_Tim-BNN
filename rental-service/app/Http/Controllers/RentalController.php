<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Http\Services\CustomerServiceClient;
use App\Http\Services\CarServiceClient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RentalController extends Controller
{
    protected $customerServiceClient;
    protected $carServiceClient;

    public function __construct(
        CustomerServiceClient $customerServiceClient,
        CarServiceClient $carServiceClient
    ) {
        $this->customerServiceClient = $customerServiceClient;
        $this->carServiceClient = $carServiceClient;
    }

    /**
     * PROVIDER: Get all rentals
     * GET /api/rentals
     */
    public function index()
    {
        $rentals = Rental::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Rentals retrieved successfully',
            'data' => $rentals
        ], 200);
    }

    /**
     * PROVIDER: Get rental by ID
     * GET /api/rentals/{id}
     */
    public function show($id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json([
                'success' => false,
                'message' => 'Rental not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental retrieved successfully',
            'data' => $rental
        ], 200);
    }

    /**
     * PROVIDER: Get rentals by customer ID
     * GET /api/rentals/customer/{customerId}
     */
    public function getByCustomer($customerId)
    {
        $rentals = Rental::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer rentals retrieved successfully',
            'data' => $rentals
        ], 200);
    }

    /**
     * CONSUMER: Create new rental
     * Memanggil CustomerService dan CarService
     * POST /api/rentals
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'car_id' => 'required|integer',
            'rental_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:rental_date'
        ]);

        // ========== CONSUMER: Ambil data customer ==========
        $customer = $this->customerServiceClient->getCustomer($request->customer_id);

        if (!$customer['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get customer data',
                'detail' => $customer['message']
            ], $customer['status'] ?? 503);
        }

        // ========== CONSUMER: Ambil data mobil ==========
        $car = $this->carServiceClient->getCar($request->car_id);

        if (!$car['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get car data',
                'detail' => $car['message']
            ], $car['status'] ?? 503);
        }

        // Cek apakah mobil tersedia
        if ($car['data']['status'] !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Car is not available for rental',
                'car_status' => $car['data']['status']
            ], 422);
        }

        // Hitung total
        $rentalDate = Carbon::parse($request->rental_date);
        $returnDate = Carbon::parse($request->return_date);
        $totalDays = $rentalDate->diffInDays($returnDate);
        $pricePerDay = (float) $car['data']['price_per_day'];
        $totalPrice = $totalDays * $pricePerDay;

        // Simpan data rental (dengan data denormalized dari service lain)
        $rental = Rental::create([
            'customer_id' => $request->customer_id,
            'car_id' => $request->car_id,
            'customer_name' => $customer['data']['name'],
            'customer_email' => $customer['data']['email'],
            'car_brand' => $car['data']['brand'],
            'car_model' => $car['data']['model'],
            'car_plate' => $car['data']['plate_number'],
            'rental_date' => $request->rental_date,
            'return_date' => $request->return_date,
            'total_days' => $totalDays,
            'total_price' => $totalPrice,
            'status' => 'active'
        ]);

        // ========== CONSUMER: Update status mobil ==========
        $carUpdate = $this->carServiceClient->updateCarStatus($request->car_id, 'rented');

        return response()->json([
            'success' => true,
            'message' => 'Rental created successfully',
            'data' => $rental,
            'service_calls' => [
                'customer_service' => $customer['success'] ? 'success' : 'failed',
                'car_service_get' => $car['success'] ? 'success' : 'failed',
                'car_service_update' => $carUpdate['success'] ? 'success' : 'failed'
            ]
        ], 201);
    }

    /**
     * CONSUMER: Return car
     * Memanggil CarService untuk update status
     * PUT /api/rentals/{id}/return
     */
    public function returnCar($id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json([
                'success' => false,
                'message' => 'Rental not found'
            ], 404);
        }

        if ($rental->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Rental is not active',
                'current_status' => $rental->status
            ], 422);
        }

        // Update status rental
        $rental->update(['status' => 'returned']);

        // ========== CONSUMER: Update status mobil jadi available ==========
        $carUpdate = $this->carServiceClient->updateCarStatus($rental->car_id, 'available');

        return response()->json([
            'success' => true,
            'message' => 'Car returned successfully',
            'data' => $rental->fresh(),
            'car_status_update' => $carUpdate['success'] ? 'success' : 'failed'
        ], 200);
    }

    /**
     * PROVIDER: Get active rentals
     * GET /api/rentals/active
     */
    public function getActiveRentals()
    {
        $rentals = Rental::where('status', 'active')->get();

        return response()->json([
            'success' => true,
            'message' => 'Active rentals retrieved successfully',
            'data' => $rentals
        ], 200);
    }
}
