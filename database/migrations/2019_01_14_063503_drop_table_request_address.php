<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableRequestAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('request_addresses');
        Schema::create('request_addresses', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->char('crud_type', 1);
            $table->integer('person_id');
            $table->string('employee_id', 50);
            $table->date('eff_begin')->nullable(true);
            $table->date('eff_end')->nullable(true);
            $table->integer('person_address_id')->nullable(true);
            $table->string('lov_rsty', 10)->nullable(true);
            $table->string('lov_rsow', 10)->nullable(true);
            $table->string('city_code', 10)->nullable(true);
            $table->string('address', 255)->nullable(true);
            $table->string('postal_code', 10)->nullable(true);
            $table->string('phone', 50)->nullable(true);
            $table->string('fax', 50)->nullable(true);
            $table->string('map_location', 255)->nullable(true);
            $table->boolean('is_default');
            $table->date('request_date')->nullable(true);
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
        Schema::dropIfExists('request_address');
    }
}
