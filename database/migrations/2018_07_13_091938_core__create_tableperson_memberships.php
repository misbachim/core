<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonMemberships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_memberships', function (Blueprint $table) {
          $table->integer('tenant_id')->nullable(true);
          $table->integer('company_id')->nullable(true);
          $table->increments('id');
          $table->date('eff_begin')->nullable(true);
          $table->date('eff_end')->nullable(true);
          $table->integer('person_id')->nullable(true);
          $table->string('lov_mbty', 10)->nullable(true);
          $table->integer('created_by')->nullable(true);;
          $table->timestampTz('created_at')->nullable(true);;
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('acc_number', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_memberships');
    }
}
