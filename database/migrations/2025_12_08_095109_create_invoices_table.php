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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fix_request_id')->constrained('fix_requests')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->OnDelete('set null');
               // حقول الفاتورة
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['قيد الانتظار', 'مدفوعة', 'ملغاة'])->default('قيد الانتظار');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};