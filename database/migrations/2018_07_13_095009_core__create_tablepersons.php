<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->increments('id');
            $table->string('id_card', 20)->nullable(true);
            $table->date('eff_begin');
            $table->date('eff_end');
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable(true);
            $table->string('birth_place', 50)->nullable(true);
            $table->date('birth_date');
            $table->string('email', 50)->nullable(true);
            $table->string('phone', 50)->nullable(true);
            $table->string('mobile', 50)->nullable(true);
            $table->string('social_media', 255)->nullable(true);
            $table->string('hobbies', 255)->nullable(true);
            $table->string('strength', 255)->nullable(true);
            $table->string('weakness', 255)->nullable(true);
            $table->integer('country_id');
            $table->string('lov_ptyp', 10);
            $table->string('lov_blod', 10)->nullable(true);
            $table->string('lov_gndr', 10);
            $table->string('lov_rlgn', 10);
            $table->string('lov_mars', 10);
            $table->integer('created_by');
            $table->timestampTz('created_at');
            $table->integer('updated_by')->nullable(true);
            $table->timestampTz('updated_at')->nullable(true);
            $table->string('file_photo', 1000)->nullable(true);
            $table->dropPrimary('persons_pkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons');
    }
}
