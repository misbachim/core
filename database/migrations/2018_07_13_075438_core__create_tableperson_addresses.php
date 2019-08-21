<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_addresses', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('person_id');
          $table->increments('id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('lov_rsty', 10);
          $table->string('lov_rsow', 10);
          $table->integer('city_id');
          $table->integer('district_id')->nullable(true);
          $table->string('address');
          $table->string('postal_code', 10)->nullable(true);
          $table->string('phone', 50)->nullable(true);
          $table->string('fax', 50)->nullable(true);
          $table->string('map_location', 50)->nullable(true);
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
        Schema::dropIfExists('person_addresses');
    }
}
