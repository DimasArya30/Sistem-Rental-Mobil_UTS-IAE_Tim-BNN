<?php

namespace App\Controllers;

use App\Models\CarModel;
use CodeIgniter\RESTful\ResourceController;

class CarController extends ResourceController
{
    protected $modelName = 'App\Models\CarModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond([
            'success' => true,
            'message' => 'Cars retrieved successfully',
            'data' => $this->model->findAll()
        ]);
    }

    public function show($id = null)
    {
        $car = $this->model->find($id);
        if (!$car) {
            return $this->failNotFound('Car not found');
        }
        return $this->respond([
            'success' => true,
            'message' => 'Car retrieved successfully',
            'data' => $car
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        if (!$data['brand'] || !$data['model']) {
            return $this->failValidationErrors(['Brand and model are required']);
        }

        $insertData = [
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => $data['year'],
            'plate_number' => $data['plate_number'],
            'price_per_day' => $data['price_per_day'],
            'status' => 'available'
        ];

        $this->model->insert($insertData);
        $newCar = $this->model->find($this->model->getInsertID());

        return $this->respondCreated([
            'success' => true,
            'message' => 'Car created successfully',
            'data' => $newCar
        ]);
    }

    public function updateStatus($id = null)
    {
        $car = $this->model->find($id);
        if (!$car) {
            return $this->failNotFound('Car not found');
        }

        $data = $this->request->getJSON(true);
        $this->model->update($id, ['status' => $data['status']]);

        return $this->respond([
            'success' => true,
            'message' => 'Car status updated',
            'data' => $this->model->find($id)
        ]);
    }

    public function getCarRenters($id = null)
    {
        $car = $this->model->find($id);
        if (!$car) {
            return $this->failNotFound('Car not found');
        }

        // Consumer: ambil data customer dari CustomerService
        $customers = json_decode(file_get_contents('http://localhost:8000/api/customers'), true);

        return $this->respond([
            'success' => true,
            'message' => 'Car with potential renters',
            'car' => $car,
            'available_customers' => $customers['data'] ?? []
        ]);
    }

    public function delete($id = null)
    {
        $car = $this->model->find($id);
        
        if (!$car) {
            return $this->failNotFound('Car not found');
        }

        // Cek kalau mobil sedang disewa, tidak boleh dihapus
        if ($car['status'] === 'rented') {
            return $this->fail('Mobil sedang disewa, tidak bisa dihapus!', 422);
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'success' => true,
            'message' => 'Car deleted successfully'
        ]);
    }
}