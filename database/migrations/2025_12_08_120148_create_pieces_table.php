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
        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // اسم القطعة مثلا فلتر زيت , باب  
            $table->decimal('price' , 10 ,2);  // السعر الحالي 
            $table->string('description')->nullable(); // وصف بسيط عن القطعة 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pieces');
    }
};