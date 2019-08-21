<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersonSocmeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_person_socmeds')) {
            Schema::create('request_person_socmeds', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->increments('id');
                $table->char('crud_type', 1);
                $table->integer('profile_request_id');
                $table->integer('person_socmed_id')->nullable(true);
                $table->string('lov_socm', 10)->nullable(true);
                $table->string('account', 255)->nullable(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_person_socmeds');
    }
}
