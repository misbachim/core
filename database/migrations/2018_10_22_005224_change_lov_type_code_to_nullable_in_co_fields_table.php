<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLovTypeCodeToNullableInCoFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('co_fields', function (Blueprint $table) {
            $table->string('lov_type_code', 10)->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('co_fields', function (Blueprint $table) {
            $table->string('lov_type_code', 10)->nullable(false)->change();
        });
    }
}
