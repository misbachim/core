<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablecfPersonBasicInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cf_person_basic_info', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->increments('id');
          $table->integer('person_id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('c1')->nullable(true);
          $table->string('c2')->nullable(true);
          $table->string('c3')->nullable(true);
          $table->string('c4')->nullable(true);
          $table->string('c5')->nullable(true);
          $table->string('c6')->nullable(true);
          $table->string('c7')->nullable(true);
          $table->string('c8')->nullable(true);
          $table->string('c9')->nullable(true);
          $table->string('c10')->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->dropPrimary('cf_person_basic_info_pkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cf_person_basic_info');
    }
}
