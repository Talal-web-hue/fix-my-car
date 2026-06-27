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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount',  12,2);  ///المبلغ المدفوع
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'mobile_payment'])->default('cash');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->string('transaction_number')->nullable(); // رقم المعاملة
            $table->timestamp('payment_date')->useCurrent(); /// تاريخ الدفع
            $table->text('notes')->nullable();  ///  ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
