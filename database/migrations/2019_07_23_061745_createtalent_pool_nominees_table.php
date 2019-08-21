<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatetalentPoolNomineesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talent_pool_nominees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->integer('talent_pool_id');
            $table->string('employee_id', 20);
            $table->string('status', 30)->default('Confirmed');
            $table->string('readiness', 50);
            $table->integer('year');
            $table->longText('note');
            $table->timestampTz('created_at');
            $table->integer('created_by');
            $table->timestampTz('updated_at')->nullable(true);
            $table->integer('updated_by')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('talent_pool_nominees');
    }
}
