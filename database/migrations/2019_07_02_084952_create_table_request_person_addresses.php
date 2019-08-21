<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersonAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_person_addresses')) {
            Schema::create('request_person_addresses', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->increments('id');
                $table->integer('profile_request_id');
                $table->char('crud_type', 1);
                $table->integer('person_address_id')->nullable(true);
                $table->string('lov_rsty', 10)->nullable(true);
                $table->string('lov_rsow', 10)->nullable(true);
                $table->string('city_code', 10)->nullable(true);
                $table->string('address', 255)->nullable(true);
                $table->string('postal_code', 10)->nullable(true);
                $table->string('phone', 50)->nullable(true);
                $table->string('fax', 50)->nullable(true);
                $table->string('map_location', 255)->nullable(true);
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
        Schema::dropIfExists('request_person_addresses');
    }
}
