<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLocationCodeAndProjectCodeAtWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'location_code')) {
                $table->string('location_code',20)->nullable(true);
            }
            if (!Schema::hasColumn('workflows', 'project_code')) {
                $table->string('project_code',20)->nullable(true);
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
            if (Schema::hasColumn('workflows', 'location_code')) {
                $table->dropColumn('location_code');
            }
            if (Schema::hasColumn('workflows', 'project_code')) {
                $table->dropColumn('project_code');
            }
        });
    }
}
