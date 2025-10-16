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
        Schema::create('enumeration_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enumeration_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_field_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable(); // store all values as text for flexibility
            $table->timestamps();

            // Ensure one value per field per enumeration
            $table->unique(['enumeration_id', 'project_field_id']);
            $table->index(['enumeration_id']);
            $table->index(['project_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enumeration_data');
    }
};
