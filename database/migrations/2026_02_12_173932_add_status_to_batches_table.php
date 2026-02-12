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
        Schema::table('batches', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('code');
            $table->integer('total_codes')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['status', 'total_codes']);
        });
    }
};
