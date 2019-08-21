<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCompanyToMtModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mt_modules', function (Blueprint $table) {
            $table->boolean('is_company')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('mt_modules', 'is_company')) {
            Schema::table('mt_modules', function (Blueprint $table) {
                $table->dropColumn('is_company');
            });
        }
    }
}
