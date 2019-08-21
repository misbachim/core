<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableposition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'is_single')) {
                $table->dropColumn('is_single');
            }
            if (Schema::hasColumn('positions', 'cost_center_code')) {
                $table->dropColumn('cost_center_code');
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
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'is_single')) {
                $table->boolean('is_single')->default(false);
            }
            if (!Schema::hasColumn('positions', 'cost_center_code')) {
                $table->string('cost_center_code')->nullable(true);
            }
        });
    }
}
