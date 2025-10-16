<?php
// Migration 4: Create pivot table for staff-project assignments (many-to-many relationship)
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
        Schema::create('project_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            // Ensure a staff member can't be assigned to the same project multiple times (unless removed and re-assigned)
            $table->unique(['project_id', 'staff_id', 'removed_at']);

            // Indexes for better query performance
            $table->index(['project_id', 'removed_at']);
            $table->index(['staff_id', 'removed_at']);
            $table->index(['assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_staff');
    }
};
