<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersonExtTrainings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_person_ext_trainings')) {
            Schema::create('request_person_ext_trainings', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->integer('profile_request_id');
                $table->increments('id');
                $table->char('crud_type', 1);
                $table->integer('person_ext_training_id')->nullable(true);
                $table->string('institution', 50)->nullable(true);
                $table->smallInteger('year_begin	')->nullable(true);
                $table->smallInteger('year_end	')->nullable(true);
                $table->string('description', 255)->nullable(true);
                $table->string('file_certificate', 1000)->nullable(true);
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
        Schema::dropIfExists('request_person_ext_trainings');
    }
}
