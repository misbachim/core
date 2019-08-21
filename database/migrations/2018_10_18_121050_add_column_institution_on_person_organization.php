<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInstitutionOnPersonOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('person_organizations', 'institution')) {
                $table->string('institution', 50)->nullable(true);
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
        Schema::table('person_organizations', function (Blueprint $table) {
            //
        });
    }
}
