<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablemtAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mt_attributes', function (Blueprint $table) {
          $table->increments('id');
          $table->string('mt_module_code', 20);
          $table->string('name', 50);
          $table->string('temp_field_name', 10)->nullable(true);
          $table->string('data_type', 50)->nullable(true);
          $table->boolean('is_mandatory');
          $table->string('default_value', 50)->nullable(true);
          $table->integer('min')->nullable(true);
          $table->integer('max')->nullable(true);
          $table->smallInteger('decimal')->nullable(true);
          $table->text('regex')->nullable(true);
          $table->boolean('is_lookup');
          $table->string('lookup_service', 50)->nullable(true);
          $table->string('lookup_table', 50)->nullable(true);
          $table->string('lookup_field', 50)->nullable(true);
          $table->boolean('is_hidden');
          $table->string('default_value_type', 50)->nullable(true);
          $table->string('lookup_condition', 50)->nullable(true);
          $table->string('dest_service', 50)->nullable(true);
          $table->string('dest_table', 50)->nullable(true);
          $table->string('dest_field', 50)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mt_attributes');
    }
}
