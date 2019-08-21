<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonWorkExps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_work_exps', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->increments('id');
          $table->integer('person_id');
          $table->date('date_begin');
          $table->date('date_end')->nullable(true);
          $table->string('company', 50);
          $table->string('job_pos', 50);
          $table->string('job_desc', 255);
          $table->string('location', 255);
          $table->string('benefit', 255)->nullable(true);
          $table->integer('last_salary');
          $table->string('reason', 255)->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_work_exps');
    }
}
