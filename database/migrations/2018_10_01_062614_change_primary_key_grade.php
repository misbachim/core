<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePrimaryKeyGrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            DB::statement('alter table "grades" drop constraint "grades_pkey" cascade');
            $table->dropColumn('id');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
          $table->dropColumn('id');
            $table->primary('code');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->integer('id')->default(0);
        });
    }
}
