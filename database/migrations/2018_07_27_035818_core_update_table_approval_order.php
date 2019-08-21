<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableApprovalOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_orders', function (Blueprint $table) {
            if (Schema::hasColumn('approval_orders', 'lov_wfty')) {
                $table->dropColumn('lov_wfty');
            }
            if (Schema::hasColumn('approval_orders', 'pos_structure_id')) {
                $table->dropColumn('pos_structure_id');
            }
            if (Schema::hasColumn('approval_orders', 'pos_structure_level')) {
                $table->dropColumn('pos_structure_level');
            }
            if (!Schema::hasColumn('approval_orders', 'workflow_id')) {
                $table->integer('workflow_id')->default(1);
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
        Schema::table('approval_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_orders', 'lov_wfty')) {
                $table->string('lov_wfty',20)->default('LEAV');
            }
            if (!Schema::hasColumn('approval_orders', 'pos_structure_id')) {
                $table->integer('pos_structure_id')->nullable(true);
            }
            if (!Schema::hasColumn('approval_orders', 'pos_structure_level')) {
                $table->smallInteger('pos_structure_level')->nullable(true);
            }
            if (Schema::hasColumn('approval_orders', 'workflow_id')) {
                $table->dropColumn('workflow_id');
            }
        });
    }
}
