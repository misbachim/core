<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAssignAndAssignTransactionPositionSlotCodeLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('position_slot_code', 50)->change();
        });

        Schema::table('assignment_transactions', function (Blueprint $table) {
            $table->string('n_position_slot_code', 50)->change();
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
            $table->string('position_slot_code', 20)->change();
        });

        Schema::table('assignment_transactions', function (Blueprint $table) {
            $table->string('n_position_slot_code', 20)->change();
        });
    }
}
