<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enumerations', function (Blueprint $table) {
            $table->timestamp('enumerated_at')->useCurrent()->change();
        });
    }

    public function down(): void
    {
        Schema::table('enumerations', function (Blueprint $table) {
            $table->timestamp('enumerated_at')->nullable()->change();
        });
    }
};
