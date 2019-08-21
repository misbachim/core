<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTableassignmentTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignment_transactions', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->increments('id');
          $table->integer('n_person_id');
          $table->date('n_eff_begin');
          $table->date('n_eff_end');
          $table->boolean('n_is_primary');
          $table->string('n_employee_id', 20);
          $table->string('n_employee_type_code', 20);
          $table->string('n_cost_center_code', 20)->nullable(true);
          $table->string('n_grade_code', 20)->nullable(true);
          $table->string('n_lov_asta', 10);
          $table->integer('n_supervisor_id')->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('n_file_assignment_doc', 1000)->nullable(true);
          $table->string('n_note', 500)->nullable(true);
          $table->string('n_assignment_doc_number', 50)->nullable(true);
          $table->boolean('is_approved');
          $table->string('n_location_code', 20);
          $table->string('n_unit_code', 20);
          $table->string('n_job_code', 20);
          $table->string('n_position_code', 20);
          $table->integer('o_assignment_id')->nullable(true);
          $table->string('n_assignment_reason_code', 20);
          $table->string('n_lov_acty', 10);
          $table->date('n_final_process_date')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignment_transactions');
    }
}
