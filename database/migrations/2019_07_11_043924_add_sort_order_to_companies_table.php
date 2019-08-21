<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSortOrderToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
        Schema::table('companies', function (Blueprint $table) {
            $companies = DB::table('companies')->select('id')->get();
            for ($i = 0; $i < count($companies); $i++) {
                DB::statement('update companies set sort_order= ' . $i . ' where id=' . $companies[$i]->id);
            }
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
            $table->dropColumn('sort_order');
        });
    }
}
