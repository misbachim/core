<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableRequestSocmeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('request_socmeds');
        Schema::create('request_socmeds', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->char('crud_type', 1);
            $table->integer('person_id');
            $table->integer('person_socmed_id')->nullable(true);
            $table->string('lov_socm', 10)->nullable(true);
            $table->string('account', 255)->nullable(true);
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
        Schema::dropIfExists('request_socmeds');
    }
}
