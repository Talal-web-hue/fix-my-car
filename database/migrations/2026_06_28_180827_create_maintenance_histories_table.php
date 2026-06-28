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
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->text('description'); // وصف الصيانة أو الإصلاح الذي تم على السيارة
            $table->date('maintenance_date'); // تاريخ الصيانة
            $table->decimal('cost', 10, 2); // تكلفة الصيانة
            $table->string('service_center')->nullable(); // اسم مركز الخدمة أو الورشة
            $table->enum('service_type', ['صيانة دورية', 'إصلاح', 'كهرباء' , 'ترقية' , 'هيكل ودهان','صيانة محرك' , 'خدمات أخرى' , 'صيانة عامة' ,'فرامل', 'تكييف' ,])->default('صيانة دورية'); // نوع الخدمة
            $table->date('service_date')->nullable(); // تاريخ الخدمة
            $table->enum('status', ['مكتملة', 'قيد الانتظار', 'ملغاة'])->default('قيد الانتظار'); // حالة الصيانة
            $table->string('notes')->nullable(); // ملاحظات إضافية حول الصيانة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_histories');
    }
};
