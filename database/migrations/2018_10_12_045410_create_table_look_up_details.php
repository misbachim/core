<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLookUpDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lookup_details', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->integer('lookup_id');
            $table->string('look_1_code', 20)->nullable(true);
            $table->string('look_2_code', 20)->nullable(true);
            $table->string('look_3_code', 20)->nullable(true);
            $table->string('look_4_code', 20)->nullable(true);
            $table->string('look_5_code', 20)->nullable(true);
            $table->integer('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lookup_details');
    }
}
