<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTableposDataAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_data_access', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id')->nullable(true);
          $table->integer('user_id');
          $table->char('menu_code', 5)->nullable(true);
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
        Schema::dropIfExists('pos_data_access');
    }
}
