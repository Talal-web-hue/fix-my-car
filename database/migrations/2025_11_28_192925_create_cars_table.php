<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plateNumber')->unique();  //رقم اللوحة
            $table->string('model');   // 2020 , 2022
            $table->string('car_manufacture'); // الشركة المصنعة
            $table->string('color');
            $table->string('vin')->unique(); // رقم الهيكل
            $table->enum('car_type' , ['بنزين' , 'مازوت' , 'كهرباء']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }


};