<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $customers = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@email.com',
                'phone' => '081234567890',
                'ktp_number' => '3201012345670001',
                'address' => 'Jl. Asia Afrika No. 1, Bandung'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@email.com',
                'phone' => '081234567891',
                'ktp_number' => '3201012345670002',
                'address' => 'Jl. Braga No. 10, Bandung'
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
