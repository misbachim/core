<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLookUps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lookups', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->string('code', 20);
            $table->string('description', 255);
            $table->string('lov_look_1', 20)->nullable(true);
            $table->string('lov_look_2', 20)->nullable(true);
            $table->string('lov_look_3', 20)->nullable(true);
            $table->string('lov_look_4', 20)->nullable(true);
            $table->string('lov_look_5', 20)->nullable(true);
            $table->date('eff_begin');
            $table->date('eff_end');
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
        Schema::dropIfExists('lookups');
    }
}
