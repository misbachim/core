<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPaklaringToTableReportTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_statuses', 'generate_paklaring')) {
                $table->boolean('generate_paklaring')->default(false);
            }

            if (!Schema::hasColumn('employee_statuses', 'report_templates_id')) {
                $table->integer('report_templates_id')->nullable(true);
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
        Schema::table('employee_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('employee_statuses', 'generate_paklaring')) {
                $table->dropColumn('generate_paklaring');
            }

            if (!Schema::hasColumn('employee_statuses', 'report_templates_id')) {
                $table->dropColumn('report_templates_id');
            }
        });
    }
}
