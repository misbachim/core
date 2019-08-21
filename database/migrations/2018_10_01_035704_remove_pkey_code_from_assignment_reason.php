<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePkeyCodeFromAssignmentReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignment_reasons', function (Blueprint $table) {
              DB::statement('alter table "assignment_reasons" drop constraint "assignment_reasons_pkey" cascade');
              $table->dropColumn('id');
        });

        Schema::table('assignment_reasons', function (Blueprint $table) {
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
        Schema::table('assignment_reasons', function (Blueprint $table) {
          $table->dropColumn('id');
            $table->primary('code');
        });

        Schema::table('assignment_reasons', function (Blueprint $table) {
            $table->integer('id')->default(0);
        });
    }
}
