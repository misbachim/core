<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableassignmentTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignment_transactions', function (Blueprint $table) {
            $table->renameColumn('n_employee_type_code', 'n_employee_status_code');
            if (!Schema::hasColumn('assignment_transactions', 'n_position_slot_code')) {
                $table->string('n_position_slot_code', 20)->default('STAFF1');
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
        Schema::table('assignment_transactions', function (Blueprint $table) {
            $table->renameColumn('n_employee_status_code', 'n_employee_type_code');
            if (Schema::hasColumn('assignment_transactions', 'n_position_slot_code')) {
                $table->dropColumn('n_position_slot_code');
            }
        });
    }
}
