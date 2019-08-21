<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProfileRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('profile_requests')) {
            Schema::create('profile_requests', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->increments('id');
                $table->integer('person_id');
                $table->char('status', 1);
                $table->timestampTz('created_at');
                $table->integer('created_by');
                $table->timestampTz('updated_at')->nullable(true);
                $table->integer('updated_by')->nullable(true);
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
        Schema::dropIfExists('profile_requests');
    }
}
