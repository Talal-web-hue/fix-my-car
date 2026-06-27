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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // الربط مع جدول السيارات
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->dateTime('appointment_date'); // تاريخ ووقت الموعد
            $table->enum('status', ['قيد الانتظار', 'مقبول', 'مرفوض', 'منتهي'])->default('قيد الانتظار');
            $table->text('notes')->nullable(); // ملاحظات العميل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
