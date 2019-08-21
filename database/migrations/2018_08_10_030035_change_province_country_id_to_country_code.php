<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProvinceCountryIdToCountryCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE provinces ALTER COLUMN code SET DATA TYPE VARCHAR(20)");

        Schema::table('provinces', function (Blueprint $table) {
            $table->renameColumn('country_id', 'country_code');
        });

        DB::statement("ALTER TABLE provinces ALTER COLUMN country_code SET DATA TYPE VARCHAR(20)");

        Schema::dropIfExists('lib_provinces');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE provinces ALTER COLUMN code SET DATA TYPE VARCHAR(5)");

        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->integer('country_id')->default(1);
        });

        Schema::create('lib_provinces', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('country_id')->nullable(true);
            $table->string('code', 2)->nullable(true);
            $table->string('name', 50)->nullable(true);
            $table->integer('created_by')->nullable(true);
            $table->timestampTz('created_at')->nullable(true);
            $table->integer('updated_by')->nullable(true);
            $table->timestampTz('updated_at')->nullable(true);
        });
    }
}
