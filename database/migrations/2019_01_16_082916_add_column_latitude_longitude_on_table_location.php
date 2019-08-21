<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLatitudeLongitudeOnTableLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'latitude')) {
                $table->double('latitude', 10,6)->nullable(true);
            }

            if (!Schema::hasColumn('locations', 'longitude')) {
                $table->double('longitude', 10,6)->nullable(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (!Schema::hasColumn('locations', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
}
