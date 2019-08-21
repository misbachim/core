<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonEducations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_educations', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('person_id');
          $table->increments('id');
          $table->date('eff_begin')->nullable(true);
          $table->date('eff_end')->nullable(true);
          $table->string('lov_edul', 10);
          $table->string('institution', 50);
          $table->string('subject', 50)->nullable(true);
          $table->decimal('grade', 10, 2);
          $table->decimal('max_grade', 10, 2);
          $table->smallInteger('year_begin');
          $table->smallInteger('year_end')->nullable(true);
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
        Schema::dropIfExists('person_educations');
    }
}
