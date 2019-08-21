<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTaleuniDataAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uni_data_access', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id')->nullable(true);
          $table->integer('user_id');
          $table->char('menu_code', 5);
          $table->string('data_access_value');
          $table->char('privilege', 1)->default('A');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uni_data_access');
    }
}
