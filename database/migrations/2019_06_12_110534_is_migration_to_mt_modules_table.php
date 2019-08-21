<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IsMigrationToMtModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('mt_modules', 'is_migration')) {
            Schema::table('mt_modules', function (Blueprint $table) {
                $table->boolean('is_migration')->default(true);
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
        if (Schema::hasColumn('mt_modules', 'is_migration')) {
            Schema::table('mt_modules', function (Blueprint $table) {
                $table->dropColumn('is_migration');
            });
        }
    }
}
