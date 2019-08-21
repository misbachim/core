<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablecompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('name', 50);
          $table->string('description', 1000)->nullable(true);
          $table->string('company_tax_number', 50);
          $table->boolean('is_deleted')->default(false);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('file_logo', 1000)->nullable(true);
          $table->string('location_code', 20)->nullable(true);
          $table->primary(array('id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
