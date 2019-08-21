<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableEmployeeIdFormatsToNumberFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_id_formats', function (Blueprint $table) {
            $table->string('lov_nbft', 20)->default('EMPID');
            $table->string('employee_status_code', 20)->nullable()->change();
            $table->primary('id');
        });
        Schema::rename('employee_id_formats', 'number_formats');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('number_formats', 'employee_id_formats');
        Schema::table('employee_id_formats', function (Blueprint $table) {
            $table->string('employee_status_code', 20)->change();
            $table->dropColumn('lov_nbft');
            $table->dropPrimary('id');
        });
    }
}
