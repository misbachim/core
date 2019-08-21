<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEmpIdToPersonIdFromPersonCredential extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_credentials', function(Blueprint $table) {
            if (Schema::hasColumn('person_credential', 'employee_id')) {
                $table->renameColumn('employee_id', 'person_id');
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
        Schema::table('person_credential', function (Blueprint $table) {
            if (Schema::hasColumn('person_credential', 'person_id')) {
                $table->renameColumn('person_id', 'employee_id');
            }
        });
    }
}
