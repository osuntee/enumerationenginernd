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
        Schema::table('enumerations', function (Blueprint $table) {
            $table->string('reference')->unique()->after('id');
            $table->longText('qrcode')->after('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enumerations', function (Blueprint $table) {
            $table->dropColumn(['reference', 'qrcode']);
        });
    }
};
