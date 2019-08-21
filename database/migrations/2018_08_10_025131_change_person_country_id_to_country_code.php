<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePersonCountryIdToCountryCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->renameColumn('country_id', 'country_code');
        });

        DB::statement("ALTER TABLE persons ALTER COLUMN country_code SET DATA TYPE VARCHAR(20)");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->integer('country_id')->nullable(true);
        });
    }
}
