<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnArg1OnTableLovAndLovType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lovs', function (Blueprint $table) {
            if (!Schema::hasColumn('lovs', 'arg1')) {
                $table->string('arg1', 20)->nullable(true);
            }
        });

        Schema::table('lov_types', function (Blueprint $table) {
            if (!Schema::hasColumn('lov_types', 'arg1')) {
                $table->string('arg1', 20)->nullable(true);
            }
        });

        DB::statement("update lov_types set arg1 = 'Alert Flag' where code='DCTY';");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lovs', function (Blueprint $table) {
            if (!Schema::hasColumn('lovs', 'arg1')) {
                $table->dropColumn('arg1');
            }
        });

        Schema::table('lov_types', function (Blueprint $table) {
            if (!Schema::hasColumn('lov_types', 'arg1')) {
                $table->dropColumn('arg1');
            }
        });
    }
}
