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
        Schema::create('project_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name'); // field name (e.g., 'car_model')
            $table->string('label'); // display label (e.g., 'Car Model')
            $table->string('type'); // input type (text, number, select, etc.)
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->text('validation_rules')->nullable(); // additional validation rules
            $table->json('options')->nullable(); // for select, radio, checkbox options
            $table->json('attributes')->nullable(); // for additional HTML attributes (min, max, step, accept, etc.)
            $table->integer('order')->default(0); // field display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure field names are unique within a project
            $table->unique(['project_id', 'name']);
            $table->index(['project_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_fields');
    }
};
