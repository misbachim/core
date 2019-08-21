<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->date('eff_begin');
          $table->date('eff_end');
          $table->string('cost_center_code', 20)->nullable(true);
          $table->string('code', 20)->primary();
          $table->string('description')->nullable(true);
          $table->boolean('is_head')->default(false);
          $table->boolean('is_single')->default(false);
          $table->integer('created_by');
          $table->timestampTz('created_at');
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('unit_code', 20);
          $table->string('job_code', 20);
          $table->string('name', 50);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('positions');
    }
}
