<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInterimAtSuccessionPool extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('succession_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('succession_pools', 'interim')) {
                $table->string('interim', 20)->nullable(true);
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
        Schema::table('succession_pools', function (Blueprint $table) {
            if (Schema::hasColumn('succession_pools', 'interim')) {
                $table->dropColumn('interim');
            }
        });
    }
}
