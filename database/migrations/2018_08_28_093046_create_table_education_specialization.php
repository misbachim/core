<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationSpecialization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('education_specializations', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->string('code', 50);
            $table->string('name', 255)->nullable(true);
            $table->string('description', 255)->nullable(true);
            $table->string('lov_category_education', 20);
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
        Schema::dropIfExists('education_specializations');
    }
}
