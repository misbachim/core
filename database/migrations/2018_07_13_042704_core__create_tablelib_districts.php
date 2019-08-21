<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablelibDistricts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lib_districts', function (Blueprint $table) {
          $table->integer('id')->primary();
          $table->integer('city_id')->nullable(true);
          $table->string('name', 50)->nullable(true);
          $table->integer('created_by')->nullable(true);
          $table->timestampTz('created_at')->nullable(true);
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
        Schema::dropIfExists('lib_districts');
    }
}
