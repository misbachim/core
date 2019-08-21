<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableRequestBasicInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('request_basic_info');
        Schema::create('request_basic_info', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->integer('person_id');
            $table->string('country_code', 10);
            $table->string('lov_rlgn', 10);
            $table->string('lov_gndr', 10);
            $table->string('lov_mars', 10);
            $table->string('email', 50)->nullable(true);
            $table->string('mobile', 50)->nullable(true);
            $table->char('status', 1);
            $table->timestampTz('created_at');
            $table->integer('created_by');
            $table->timestampTz('updated_at')->nullable(true);
            $table->integer('updated_by')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_basic_info');
    }
}
