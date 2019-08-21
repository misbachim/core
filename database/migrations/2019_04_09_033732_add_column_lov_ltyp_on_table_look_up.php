<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLovLtypOnTableLookUp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('lookups', 'lov_ltyp')) {
            Schema::table('lookups', function (Blueprint $table) {
                $table->string('lov_ltyp',10)->default('PYR');
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
        if (Schema::hasColumn('lookups', 'lov_ltyp')) {
            Schema::table('lookups', function (Blueprint $table) {
                $table->dropColumn('lov_ltyp');
            });
        }
    }
}
