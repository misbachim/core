<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonFamilies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_families', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('person_id');
          $table->increments('id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('lov_famr', 10);
          $table->string('name', 50);
          $table->string('lov_gndr', 10);
          $table->date('birth_date');
          $table->string('lov_edul', 10);
          $table->string('occupation', 50)->nullable(true);
          $table->string('description')->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->boolean('is_emergency')->nullable(true);
          $table->string('address')->nullable(true);
          $table->string('phone', 50)->nullable(true);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_families');
    }
}
