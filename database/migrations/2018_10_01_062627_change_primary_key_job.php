<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePrimaryKeyJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            DB::statement('alter table "jobs" drop constraint "jobs_pkey" cascade');
            $table->dropColumn('id');
        });

        Schema::table('jobs', function (Blueprint $table) {
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
        Schema::table('jobs', function (Blueprint $table) {
            DB::statement('alter table "jobs" drop constraint "jobs_pkey" cascade');
            $table->dropColumn('id');
            $table->primary('code');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('id')->default(0);
        });
    }
}
