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
        Schema::table('accesses', function (Blueprint $table) {
            $table->unsignedBigInteger('code_id')->nullable()->after('ticket_id');
            $table->integer('code_discount')->nullable()->after('phone');
            $table->string('code_name')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accesses', function (Blueprint $table) {
            $table->dropColumn('code_id');
            $table->dropColumn('code_name');
            $table->dropColumn('code_discount');
        });
    }
};
