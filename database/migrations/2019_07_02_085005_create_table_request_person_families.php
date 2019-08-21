<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersonFamilies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_person_families')) {
            Schema::create('request_person_families', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->increments('id');
                $table->integer('profile_request_id');
                $table->char('crud_type', 1);
                $table->integer('person_family_id')->nullable(true);
                $table->string('lov_famr', 10)->nullable(true);
                $table->string('name', 50)->nullable(true);
                $table->string('lov_gndr', 10)->nullable(true);
                $table->date('birth_date')->nullable(true);
                $table->string('lov_edul', 10)->nullable(true);
                $table->string('occupation', 50)->nullable(true);
                $table->string('address', 255)->nullable(true);
                $table->string('phone', 50)->nullable(true);
                $table->boolean('is_emergency');
                $table->string('description', 255)->nullable(true);
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
        Schema::dropIfExists('request_person_families');
    }
}
