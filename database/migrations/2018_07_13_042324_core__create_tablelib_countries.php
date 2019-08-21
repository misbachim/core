<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablelibCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lib_countries', function (Blueprint $table) {
          $table->integer('id')->primary();
          $table->char('code', 2)->nullable(true);
          $table->string('name', 50)->nullable(true);
          $table->string('dial_code', 5)->nullable(true);
          $table->string('nationality', 20)->nullable(true);
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
        Schema::dropIfExists('lib_countries');
    }
}
