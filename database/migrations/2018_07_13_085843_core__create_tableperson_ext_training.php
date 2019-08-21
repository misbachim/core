<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonExtTraining extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_ext_training', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('person_id');
          $table->increments('id');
          $table->smallInteger('year_begin');
          $table->smallInteger('year_end');
          $table->string('institution', 50);
          $table->string('description', 255);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('file_certificate', 1000)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_ext_training');
    }
}
