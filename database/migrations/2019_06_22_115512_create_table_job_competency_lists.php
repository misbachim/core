<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJobCompetencyLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_competency_lists', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->integer('job_competency_id')->nullable(true);
            $table->string('job_code');
            $table->string('competency_code', 20);
            $table->boolean('essential')->default(false);
            $table->integer('rating_scale_detail_id');
            $table->boolean('use_in_review')->default(false);
            $table->integer('margin_value');
            $table->date('eff_begin');
            $table->date('eff_end');
            $table->string('margin_level', 20);
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
        Schema::dropIfExists('job_competency_lists');
    }
}
