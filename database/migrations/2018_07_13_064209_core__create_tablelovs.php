<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablelovs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lovs', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->string('lov_type_code', 10);
          $table->string('key_data', 10);
          $table->string('val_data', 100);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->boolean('is_disableable')->default(false);
          $table->boolean('is_active');
          $table->primary(array('tenant_id', 'company_id', 'lov_type_code', 'key_data'));
          $table->unique(array('tenant_id', 'company_id', 'lov_type_code', 'key_data'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lovs');
    }
}
