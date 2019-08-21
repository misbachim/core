<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonSocmeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_socmeds', function (Blueprint $table) {
          $table->integer('tenant_id')->nullable(true);
          $table->increments('id');
          $table->integer('person_id')->nullable(true);
          $table->string('lov_socm', 10)->nullable(true);
          $table->string('account', 255)->nullable(true);
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
        Schema::dropIfExists('person_socmeds');
    }
}
