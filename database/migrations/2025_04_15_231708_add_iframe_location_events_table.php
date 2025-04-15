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
        Schema::table('location_events', function (Blueprint $table) {
            $table->text('address')->nullable()->change();
            $table->double('latitude')->nullable()->change();
            $table->double('longitude')->nullable()->change();
            $table->longText('iframe')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
