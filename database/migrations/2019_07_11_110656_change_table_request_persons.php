<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableRequestPersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_persons', function (Blueprint $table) {
            $table->dropColumn('profile_request_id	');
            $table->integer('profile_request_id');
            $table->string('id_card', 20)->nullable(true)->change();
            $table->string('last_name', 50)->nullable(true)->change();
            $table->string('birth_place', 50)->nullable(true)->change();
            $table->string('lov_blod', 10)->nullable(true)->change();
            $table->string('email', 50)->nullable(true)->change();
            $table->string('phone', 50)->nullable(true)->change();
            $table->string('mobile', 50)->nullable(true)->change();
            $table->string('hobbies', 255)->nullable(true)->change();
            $table->string('strength', 255)->nullable(true)->change();
            $table->string('weakness', 255)->nullable(true)->change();
            $table->string('file_photo', 1,000)->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_persons', function (Blueprint $table) {
            $table->dropColumn('profile_request_id');
            $table->integer('profile_request_id	');
            $table->string('id_card', 20)->change();
            $table->string('last_name', 50)->change();
            $table->string('birth_place', 50)->change();
            $table->string('lov_blod', 10)->change();
            $table->string('email', 50)->change();
            $table->string('phone', 50)->change();
            $table->string('mobile', 50)->change();
            $table->string('hobbies', 255)->change();
            $table->string('strength', 255)->change();
            $table->string('weakness', 255)->change();
            $table->string('file_photo', 1,000)->change();
        });
    }
}
