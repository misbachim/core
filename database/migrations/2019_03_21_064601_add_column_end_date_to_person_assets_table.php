<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEndDateToPersonAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_assets', function (Blueprint $table) {
            $table->date('end_date')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_assets', function (Blueprint $table) {
            if (Schema::hasColumn('person_assets','end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
