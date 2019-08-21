<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnAppCodeInWidgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('widget', 'app_code')) {
            Schema::table('widget', function (Blueprint $table) {
                $table->dropColumn('app_code');
            });
        }

        Schema::table('widget', function (Blueprint $table) {
            $table->enum('app_code', ['ADMIN', 'ESS', 'MOBILE', 'WEB'])->nullable();

            $table->integer('modules_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('widget', 'app_code')) {
            Schema::table('widget', function (Blueprint $table) {
                $table->dropColumn('app_code');
            });
        }

        Schema::table('widget', function (Blueprint $table) {
            $table->enum('app_code', ['DESKTOP', 'ESS', 'MOBILE'])->nullable();

            $table->dropColumn('modules_id');
        });
    }
}
