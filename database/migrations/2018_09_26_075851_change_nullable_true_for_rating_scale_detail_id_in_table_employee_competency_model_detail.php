<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNullableTrueForRatingScaleDetailIdInTableEmployeeCompetencyModelDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_competency_model_details', function (Blueprint $table) {
            $table->integer('rating_scale_detail_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_competency_model_details', function (Blueprint $table) {
            $table->integer('rating_scale_detail_id')->nullable(false)->change();
        });
    }
}
