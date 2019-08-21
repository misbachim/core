<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmploymentCodeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('person_memberships')) {
            Schema::table('person_memberships', function (Blueprint $table) {
                $table->string('employment_code', 20)->nullable(true);
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
        if (Schema::hasTable('person_memberships')) {
            Schema::table('person_memberships', function (Blueprint $table) {
                $table->dropColumn('employment_code');
            });
        }
    }
}
