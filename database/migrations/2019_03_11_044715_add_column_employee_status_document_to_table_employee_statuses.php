<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEmployeeStatusDocumentToTableEmployeeStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_statuses', 'employee_status_document_templates_id')) {
                $table->integer('employee_status_document_templates_id')->nullable(true);
            }
            $table->renameColumn('report_templates_id', 'paklaring_templates_id');
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
            if (Schema::hasColumn('employee_statuses', 'employee_status_document_templates_id')) {
                $table->dropColumn('employee_status_document_templates_id');
            }
            $table->renameColumn('paklaring_templates_id', 'report_templates_id');
        });
    }
}
