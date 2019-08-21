<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnReadinessToReadinessIdInSuccessorListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('successor_lists', 'readiness')) {
            Schema::table('successor_lists', function (Blueprint $table) {
                $table->dropColumn('readiness');
                $table->integer('readiness_id')->nullable(true);
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('successor_lists', 'readiness_id')) {
            Schema::table('successor_lists', function (Blueprint $table) {
                $table->dropColumn('readiness_id');
                $table->string('readiness', 50)->nullable(true);
            });
        }
    }
}
