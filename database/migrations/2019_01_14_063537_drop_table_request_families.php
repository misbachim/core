<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableRequestFamilies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('request_families');
        Schema::create('request_families', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->char('crud_type', 1);
            $table->integer('person_id');
            $table->string('employee_id', 50);
            $table->integer('person_family_id')->nullable(true);
            $table->string('lov_famr', 10)->nullable(true);
            $table->string('name', 50)->nullable(true);
            $table->string('lov_gndr', 10)->nullable(true);
            $table->date('birth_date')->nullable(true);
            $table->date('eff_begin')->nullable(true);
            $table->date('eff_end')->nullable(true);
            $table->string('lov_edul', 10)->nullable(true);
            $table->string('occupation', 50)->nullable(true);
            $table->string('address', 255)->nullable(true);
            $table->string('phone', 50)->nullable(true);
            $table->boolean('is_emergency');
            $table->string('description', 255)->nullable(true);
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
        Schema::dropIfExists('request_families');
    }
}
