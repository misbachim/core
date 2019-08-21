<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTableQualificationSourcesToProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qualification_sources', function (Blueprint $table) {
            Schema::rename('qualification_sources', 'providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qualification_sources', function (Blueprint $table) {
            Schema::rename('providers', 'qualification_sources');
        });
    }
}
