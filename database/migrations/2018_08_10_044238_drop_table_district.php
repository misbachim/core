<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableDistrict extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('districts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->integer('city_id');
            $table->string('name', 50);
            $table->integer('created_by');
            $table->timestampTz('created_at');
            $table->integer('updated_by')->nullable(true);
            $table->timestampTz('updated_at')->nullable(true);
        });
    }
}
