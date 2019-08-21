<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablelocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->increments('id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('name', 50);
          $table->string('description', 255)->nullable(true);
          $table->string('tax_office_code', 5)->nullable(true);
          $table->smallInteger('calendar_id')->nullable(true);
          $table->integer('city_id');
          $table->integer('district_id')->nullable(true);
          $table->string('address', 255)->nullable(true);
          $table->string('postal_code', 10)->nullable(true);
          $table->string('phone', 50)->nullable(true);
          $table->string('fax', 50)->nullable(true);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('code', 20);
          $table->string('calendar_code', 20)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
