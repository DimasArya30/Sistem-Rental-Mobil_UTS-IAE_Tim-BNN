<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_id',
        'customer_name',
        'customer_email',
        'car_brand',
        'car_model',
        'car_plate',
        'rental_date',
        'return_date',
        'total_days',
        'total_price',
        'status'
    ];

    protected $casts = [
        'rental_date' => 'date',
        'return_date' => 'date',
        'total_price' => 'decimal:2'
    ];
}
