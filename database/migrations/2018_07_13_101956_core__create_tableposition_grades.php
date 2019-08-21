<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepositionGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_grades', function (Blueprint $table) {
          $table->integer('tenant_id');
          $table->integer('company_id');
          $table->string('position_code', 20);
          $table->string('grade_code', 20);
          $table->integer('bottom_rate')->nullable(true);
          $table->integer('mid_rate')->nullable(true);
          $table->integer('top_rate')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_grades');
    }
}
