<?php
// Migration 3: Update staff table - remove project_id and add customer_id and staff_type
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
        Schema::table('staff', function (Blueprint $table) {
            // Drop existing foreign key and index
            $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id', 'is_active']);

            // Remove project_id column
            $table->dropColumn('project_id');

            // Add new columns
            $table->foreignId('customer_id')->after('id')->constrained()->onDelete('cascade');
            $table->enum('staff_type', ['super_admin', 'admin', 'user'])->after('phone')->default('user');

            // Add new indexes
            $table->index(['customer_id', 'staff_type', 'is_active']);
            $table->index(['staff_type', 'is_active']); // For querying super_admins
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Drop new foreign key and indexes
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id', 'staff_type', 'is_active']);
            $table->dropIndex(['staff_type', 'is_active']);

            // Remove new columns
            $table->dropColumn(['customer_id', 'staff_type']);

            // Add back project_id
            $table->foreignId('project_id')->after('id')->constrained()->onDelete('cascade');
            $table->index(['project_id', 'is_active']);
        });
    }
};
