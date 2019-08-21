<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSuccessorLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('successor_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->integer('succession_pool_id');
            $table->string('employee_id', 20);
            $table->string('readiness', 50)->nullable(true);
            $table->string('note', 255)->nullable(true);
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
        Schema::dropIfExists('successor_lists');
    }
}
