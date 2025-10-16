
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Payment name/description
            $table->decimal('amount', 10, 2); // Payment amount
            $table->enum('frequency', ['one_off', 'weekly', 'monthly', 'yearly']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_partial_payments')->default(false); // Can be paid in parts
            $table->enum('payment_type', ['manual', 'gateway', 'both'])->default('manual'); // How payment is processed
            $table->date('start_date')->nullable(); // When this payment starts being applicable
            $table->date('end_date')->nullable(); // When this payment stops being applicable
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_payments');
    }
};
