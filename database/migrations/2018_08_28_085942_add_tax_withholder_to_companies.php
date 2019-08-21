<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxWithholderToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tax_withholder_number', 50)->nullable(true)->default('11.111.111.1-111.111');
            $table->string('tax_withholder_name', 50)->nullable(true)->default('aaaaaaa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('tax_withholder_number');
            $table->dropColumn('tax_withholder_name');
        });
    }
}
