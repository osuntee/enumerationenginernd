<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enumeration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enumeration_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_payment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_due', 10, 2); // Amount due (might differ from base amount due to calculations)
            $table->decimal('amount_paid', 10, 2)->default(0); // Amount actually paid
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'waived'])->default('pending');
            $table->date('due_date');
            $table->datetime('paid_at')->nullable();
            $table->foreignId('paid_by_staff_id')->nullable()->constrained('staff')->nullOnDelete(); // Staff who recorded the payment
            $table->string('payment_method')->nullable(); // cash, bank_transfer, mobile_money, etc.
            $table->string('payment_reference')->nullable(); // Transaction reference
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['enumeration_id']);
            $table->index(['project_payment_id']);
            $table->index(['status', 'due_date']);
            $table->index(['due_date']);
            $table->unique(['enumeration_id', 'project_payment_id', 'due_date'], 'unique_enumeration_payment_due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('enumeration_payments');
    }
};
