<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserIdToRoleIdFromAllDataAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_data_access', function (Blueprint $table) {
            $table->renameColumn('user_id', 'role_id');
        });
        Schema::table('uni_data_access', function (Blueprint $table) {
            $table->renameColumn('user_id', 'role_id');
        });
        Schema::table('pos_data_access', function (Blueprint $table) {
            $table->renameColumn('user_id', 'role_id');
        });
        Schema::rename('job_data_access', 'data_access_job');
        Schema::rename('uni_data_access', 'data_access_uni');
        Schema::rename('pos_data_access', 'data_access_pos');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_access_pos', function (Blueprint $table) {
            $table->renameColumn('role_id', 'user_id');
        });
        Schema::table('data_access_uni', function (Blueprint $table) {
            $table->renameColumn('role_id', 'user_id');
        });
        Schema::table('data_access_job', function (Blueprint $table) {
            $table->renameColumn('role_id', 'user_id');
        });
        Schema::rename('data_access_pos', 'pos_data_access');
        Schema::rename('data_access_uni', 'uni_data_access');
        Schema::rename('data_access_job', 'job_data_access');
    }
}
