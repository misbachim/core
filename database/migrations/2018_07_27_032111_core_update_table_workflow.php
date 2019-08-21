<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropPrimary('workflows_pkey');
        });
        Schema::table('workflows', function (Blueprint $table) {

            if (Schema::hasColumn('workflows', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (!Schema::hasColumn('workflows', 'id')) {
                $table->increments('id');
            }
            if (!Schema::hasColumn('workflows', 'is_default')) {
                $table->boolean('is_default')->default(true);
            }
            if (!Schema::hasColumn('workflows', 'unit_code')) {
                $table->string('unit_code',20)->nullable(true);
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
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (Schema::hasColumn('workflows', 'id')) {
                $table->dropColumn('id');
            }
            if (Schema::hasColumn('workflows', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('workflows', 'unit_code')) {
                $table->dropColumn('unit_code');
            }
        });
        Schema::table('workflows', function (Blueprint $table) {
            $table->primary(array('tenant_id', 'company_id', 'lov_wfty'));
        });
    }
}
