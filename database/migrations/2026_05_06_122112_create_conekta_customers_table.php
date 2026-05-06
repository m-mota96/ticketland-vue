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
        Schema::create('conekta_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id', 300)->unique()->comment('Id del usuario en Conekta');
            $table->string('email', 500)->unique();
            $table->string('name', 500);
            $table->string('phone', 25)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conekta_customers');
    }
};
