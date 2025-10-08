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
        Schema::create('paypal_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->string('token_type');
            $table->integer('expires_in');
            $table->timestamp('created_at_token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_tokens');
    }
};
