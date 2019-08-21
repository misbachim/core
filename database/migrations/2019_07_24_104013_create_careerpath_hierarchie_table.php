<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareerpathHierarchieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_path_hierarchies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->integer('career_path_id');
            $table->string('position_code');
            $table->string('parent_position_code');
            $table->string('status_of_parent');
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
        Schema::dropIfExists('career_path_hierarchies');
    }
}
