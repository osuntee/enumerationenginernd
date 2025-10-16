<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enumerations', function (Blueprint $table) {
            // Remove the old enumerated_by string column
            $table->dropColumn('enumerated_by');

            // Add the new staff_id foreign key
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');

            $table->index('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enumerations', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropIndex(['staff_id']);
            $table->dropColumn('staff_id');

            // Restore the old enumerated_by string column
            $table->string('enumerated_by')->nullable();
        });
    }
};
