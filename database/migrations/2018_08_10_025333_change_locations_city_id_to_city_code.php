<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLocationsCityIdToCityCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function($table) {
            $table->dropColumn('district_id');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->renameColumn('city_id', 'city_code');
        });

        DB::statement("ALTER TABLE locations ALTER COLUMN city_code SET DATA TYPE VARCHAR(20)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->integer('district_id')->nullable(true);
            $table->dropColumn('city_code');
            $table->integer('city_id')->nullable(true);
        });
    }
}
