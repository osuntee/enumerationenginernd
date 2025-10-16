<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('enumeration_payments', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['paid_by_staff_id']);

            // Then drop the column
            $table->dropColumn('paid_by_staff_id');
        });
    }

    public function down()
    {
        Schema::table('enumeration_payments', function (Blueprint $table) {
            $table->foreignId('paid_by_staff_id')
                ->nullable()
                ->constrained('staff')
                ->nullOnDelete();
        });
    }
};
