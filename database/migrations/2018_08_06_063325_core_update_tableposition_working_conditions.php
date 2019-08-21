<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTablepositionWorkingConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('position_working_conditions', function (Blueprint $table) {
            if (!Schema::hasColumn('position_working_conditions', 'eff_begin')) {
                $table->date('eff_begin')->default('2017-01-01');
            }
            if (!Schema::hasColumn('position_working_conditions', 'eff_end')) {
                $table->date('eff_end')->default('9999-12-31');
            }
            if (!Schema::hasColumn('position_working_conditions', 'is_essential')) {
                $table->boolean('is_essential')->default(false);
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
        Schema::table('position_working_conditions', function (Blueprint $table) {
            if (Schema::hasColumn('position_working_conditions', 'eff_begin')) {
                $table->dropColumn('eff_begin');
            }
            if (Schema::hasColumn('position_working_conditions', 'eff_end')) {
                $table->dropColumn('eff_end');
            }
            if (Schema::hasColumn('position_working_conditions', 'is_essential')) {
                $table->dropColumn('is_essential');
            }
        });
    }
}
