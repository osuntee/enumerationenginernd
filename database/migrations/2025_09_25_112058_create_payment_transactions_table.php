<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enumeration_payment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->enum('type', ['payment', 'refund', 'adjustment'])->default('payment');
            $table->string('payment_method'); // cash, bank_transfer, mobile_money, etc.
            $table->string('reference')->nullable(); // Transaction reference
            $table->string('gateway_transaction_id')->nullable(); // Gateway transaction ID
            $table->json('gateway_response')->nullable(); // Gateway response data
            $table->enum('payment_source', ['manual', 'gateway'])->default('manual'); // How this transaction was made
            $table->foreignId('recorded_by_staff_id')->nullable()->constrained('staff')->nullOnDelete(); // Staff who recorded this transaction (null for gateway payments)
            $table->datetime('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['enumeration_payment_id']);
            $table->index(['transaction_date']);
            $table->index(['recorded_by_staff_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};
