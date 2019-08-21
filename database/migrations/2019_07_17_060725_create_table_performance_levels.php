<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePerformanceLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('performance_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->integer('default_value');
            $table->boolean('sync_with_review')->default(false);
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
        Schema::dropIfExists('performance_levels');
    }
}
