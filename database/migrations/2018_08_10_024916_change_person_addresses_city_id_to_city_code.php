<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePersonAddressesCityIdToCityCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_addresses', function($table) {
            $table->dropColumn('district_id');
        });

        Schema::table('person_addresses', function (Blueprint $table) {
            $table->renameColumn('city_id', 'city_code');
        });

        DB::statement("ALTER TABLE person_addresses ALTER COLUMN city_code SET DATA TYPE VARCHAR(20)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->integer('district_id')->nullable(true);
            $table->dropColumn('city_code');
            $table->integer('city_id')->nullable(true);
        });
    }
}
