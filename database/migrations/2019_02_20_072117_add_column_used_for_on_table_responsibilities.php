<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUsedForOnTableResponsibilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('responsibilities', function (Blueprint $table) {
            if (!Schema::hasColumn('responsibilities', 'used_for')) {
                $table->string('used_for', 2)->nullable(true);
            }
            if (!Schema::hasColumn('responsibilities', 'used_for')) {
                $table->string('used_for_value', 20)->nullable(true);
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
        Schema::table('responsibilities', function (Blueprint $table) {
            if (!Schema::hasColumn('responsibilities', 'used_for')) {
                $table->dropColumn('used_for');
            }
            if (!Schema::hasColumn('responsibilities', 'used_for_value')) {
                $table->dropColumn('used_for_value');
            }
        });
    }
}
