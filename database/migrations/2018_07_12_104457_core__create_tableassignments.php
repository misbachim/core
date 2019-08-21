<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTableassignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->increments('id');
          $table->integer('person_id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->boolean('is_primary');
          $table->string('employee_id', 20);
          $table->string('employee_type_code', 20);
          $table->string('cost_center_code', 20)->nullable(true);
          $table->string('grade_code', 20)->nullable(true);
          $table->string('lov_asta', 10);
          $table->integer('supervisor_id')->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('file_assignment_doc', 1000)->nullable(true);
          $table->string('note', 500)->nullable(true);
          $table->date('final_process_date')->nullable(true);
          $table->string('assignment_doc_number', 50)->nullable(true);
          $table->string('location_code', 20);
          $table->string('position_code', 20);
          $table->string('job_code', 20);
          $table->string('unit_code', 20);
          $table->string('assignment_reason_code', 20);
          $table->string('lov_acty', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignments');
    }
}
