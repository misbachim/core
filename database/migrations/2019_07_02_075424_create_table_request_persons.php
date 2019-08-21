<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_persons')) {
            Schema::create('request_persons', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->increments('id');
                $table->integer('profile_request_id	');
                $table->string('id_card', 20);
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('birth_place', 50);
                $table->date('birth_date');
                $table->string('email', 50);
                $table->string('phone', 50);
                $table->string('mobile', 50);
                $table->string('hobbies', 255);
                $table->string('strength', 255);
                $table->string('weakness', 255);
                $table->string('country_code', 20);
                $table->string('lov_blod', 10);
                $table->string('lov_rlgn', 10);
                $table->string('lov_gndr', 10);
                $table->string('lov_mars', 10);
                $table->string('file_photo', 1,000);
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
        Schema::dropIfExists('request_persons');
    }
}
