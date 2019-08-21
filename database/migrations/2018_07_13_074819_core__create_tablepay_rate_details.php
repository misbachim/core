<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepayRateDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_rate_details', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->integer('grade_id');
          $table->integer('pay_rate_id');
          $table->integer('bottom_rate');
          $table->integer('top_rate');
          $table->unique(array('tenant_id', 'company_id', 'grade_id', 'pay_rate_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_rate_details');
    }
}
