<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTablejobResponsibilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_responsibilities', function (Blueprint $table) {
            if (!Schema::hasColumn('job_responsibilities', 'eff_begin')) {
                $table->date('eff_begin')->default('2017-01-01');
            }
            if (!Schema::hasColumn('job_responsibilities', 'eff_end')) {
                $table->date('eff_end')->default('9999-12-31');
            }
            if (!Schema::hasColumn('job_responsibilities', 'is_appraisal')) {
                $table->boolean('is_appraisal')->default(false);
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
        Schema::table('job_responsibilities', function (Blueprint $table) {
            if (Schema::hasColumn('job_responsibilities', 'eff_begin')) {
                $table->dropColumn('eff_begin');
            }
            if (Schema::hasColumn('job_responsibilities', 'eff_end')) {
                $table->dropColumn('eff_end');
            }
            if (Schema::hasColumn('job_responsibilities', 'is_appraisal')) {
                $table->dropColumn('is_appraisal');
            }
        });
    }
}
