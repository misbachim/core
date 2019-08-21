<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTableapprovalOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_orders', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->string('lov_wfty', 10);
          $table->smallInteger('number');
          $table->string('lov_wapt', 10);
          $table->integer('pos_structure_id')->nullable(true);
          $table->smallInteger('pos_structure_level')->nullable(true);
          $table->string('value', 50)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_orders');
    }
}
