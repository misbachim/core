<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCandidateReadyToHireId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->integer('candidate_ready_to_hire_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('persons', 'candidate_ready_to_hire_id')) {
            Schema::table('persons', function (Blueprint $table) {
                $table->dropColumn('candidate_ready_to_hire_id');
            });
        }
    }
}
