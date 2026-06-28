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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // معرف المستخدم الذي قام بالمراجعة
            $table->foreignId('fix_request_id')->constrained('fix_requests')->onDelete('cascade'); // معرف طلب الصيانة الذي تمت مراجعته
            $table->tinyInteger('rating')->unsigned(); // التقييم من 1 إلى 5 مثلا
            $table->text('comment')->nullable(); // التعليق على الخدمة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
