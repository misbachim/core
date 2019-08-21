<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablenumberFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('number_format', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->string('lov_nbft', 10);
          $table->string('format', 50);
          $table->integer('ordinal');
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->unique(array('tenant_id', 'company_id', 'lov_nbft'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('number_format');
    }
}
