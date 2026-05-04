<?php

namespace App\Models;

use CodeIgniter\Model;

class CarModel extends Model
{
    protected $table      = 'cars';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = ['brand', 'model', 'year', 'plate_number', 'price_per_day', 'status'];
    protected $useTimestamps = false;
}