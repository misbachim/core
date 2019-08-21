<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableassignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->renameColumn('employee_type_code', 'employee_status_code');
            if (!Schema::hasColumn('assignments', 'position_slot_code')) {
                $table->string('position_slot_code', 20)->default('STAFF1');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->renameColumn('employee_status_code', 'employee_type_code');
            if (Schema::hasColumn('assignments', 'position_slot_code')) {
                $table->dropColumn('position_slot_code');
            }
        });
    }
}
