<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCompanySettingAndSettingType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'fix_value')) {
                $table->integer('fix_value')->nullable(true);
            }
        });

        Schema::table('setting_types', function (Blueprint $table) {
            if (!Schema::hasColumn('setting_types', 'vtype')) {
                $table->char('vtype',1)->default('L');
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
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'fix_value')) {
                $table->dropColumn('fix_value');
            }
        });

        Schema::table('setting_types', function (Blueprint $table) {
            if (Schema::hasColumn('setting_types', 'vtype')) {
                $table->dropColumn('vtype');
            }
        });
    }
}
