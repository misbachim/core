<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTablejobGradesAddMidRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_grades', function (Blueprint $table) {
            if (!Schema::hasColumn('job_grades', 'mid_rate')) {
                $table->integer('mid_rate')->default(0);
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
        Schema::table('job_grades', function (Blueprint $table) {
            if (Schema::hasColumn('job_grades', 'mid_rate')) {
                $table->dropColumn('mid_rate');
            }
        });
    }
}
