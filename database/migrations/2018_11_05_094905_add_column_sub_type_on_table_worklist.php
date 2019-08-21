<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSubTypeOnTableWorklist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('worklists', function (Blueprint $table) {
            if (!Schema::hasColumn('worklists', 'sub_type')) {
                $table->string('sub_type', 20)->nullable(true);
            }
            if (!Schema::hasColumn('worklists', 'description')) {
                $table->string('description', 255)->nullable(true);
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
        Schema::table('worklists', function (Blueprint $table) {
            //
        });
    }
}
