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
        Schema::create('enumerations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->timestamp('enumerated_at')->nullable(); // when the data was collected
            $table->string('enumerated_by')->nullable(); // who collected the data
            $table->text('notes')->nullable(); // additional notes
            $table->boolean('is_verified')->default(false); // verification status
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
            $table->index(['project_id', 'enumerated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enumerations');
    }
};
