<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupervisorIdProjectmanagerIdLocationCodeToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('supervisor_id',20)->nullable(true);
            $table->string('projectmanager_id',20)->default('');
            $table->string('location_code',20)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('projects', 'supervisor_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('supervisor_id');
            });
        }
        if (Schema::hasColumn('projects', 'projectmanager_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('projectmanager_id');
            });
        }
        if (Schema::hasColumn('projects', 'location_code')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('location_code');
            });
        }
    }
}
