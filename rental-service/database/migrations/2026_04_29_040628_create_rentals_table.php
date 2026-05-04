<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();

            // ❌ Tidak pakai foreign key (beda service)
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('car_id');

            // 🔥 Denormalized data (ambil dari service lain)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('car_brand')->nullable();
            $table->string('car_model')->nullable();
            $table->string('car_plate')->nullable();

            // 📅 Data rental
            $table->date('rental_date');
            $table->date('return_date');

            // 💰 Perhitungan
            $table->integer('total_days');
            $table->decimal('total_price', 12, 2);

            // 📌 Status
            $table->enum('status', ['active', 'returned', 'cancelled'])->default('active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};
