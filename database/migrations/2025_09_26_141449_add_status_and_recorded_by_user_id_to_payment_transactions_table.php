<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Add status column
            $table->enum('status', ['pending', 'success', 'failed'])
                ->default('pending')
                ->after('notes');

            // Add recorded_by_user_id as foreign key
            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->after('payment_source')
                ->constrained('users')
                ->nullOnDelete();

            // Optional: add index for faster lookups
            $table->index('recorded_by_user_id');
        });
    }

    public function down()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['recorded_by_user_id']);

            // Then drop the columns
            $table->dropColumn(['status', 'recorded_by_user_id']);
        });
    }
};
