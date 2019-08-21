<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCitiesProvinceIdToProvinceCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE cities ALTER COLUMN code SET DATA TYPE VARCHAR(20)");

        Schema::table('cities', function (Blueprint $table) {
            $table->renameColumn('province_id', 'province_code');
        });

        DB::statement("ALTER TABLE cities ALTER COLUMN province_code SET DATA TYPE VARCHAR(20)");

        Schema::table('cities', function($table) {
            $table->dropColumn('dial_code');
        });

        Schema::dropIfExists('lib_cities');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE cities ALTER COLUMN code SET DATA TYPE VARCHAR(5)");

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('province_code');
            $table->integer('province_id')->nullable(true);
            $table->string('dial_code')->nullable(true);
        });

        Schema::create('lib_cities', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('province_id')->nullable(true);
            $table->string('code', 5)->nullable(true);
            $table->string('name', 50)->nullable(true);
            $table->string('dial_code', 5)->nullable(true);
            $table->integer('created_by')->nullable(true);
            $table->timestampTz('created_at')->nullable(true);
            $table->integer('updated_by')->nullable(true);
            $table->timestampTz('updated_at')->nullable(true);
        });
    }
}
