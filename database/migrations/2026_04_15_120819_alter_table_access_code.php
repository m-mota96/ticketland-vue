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
        Schema::table('access_code', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('ticket_price');
            $table->renameColumn('discount', 'code_discount');
            $table->string('code_name')->after('code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_code', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_price', 11);
            $table->renameColumn('code_discount', 'discount');
            $table->dropColumn('code_name');
        });
    }
};
