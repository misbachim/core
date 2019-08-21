<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePositionCompetencyModelDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_competency_model_details', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->integer('position_competency_model_id');
            $table->integer('competency_id');
            $table->integer('rating_scale_detail_id')->nullable(true);
            $table->boolean('used_in_process_review');
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
        Schema::dropIfExists('position_competency_model_details');
    }
}
