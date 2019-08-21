<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnStatusOfParentCareerPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('career_path_hierarchies', function (Blueprint $table) {
            $table->dropColumn('status_of_parent');
            $table->dropColumn('parent_position_code');
        });

        Schema::table('career_path_hierarchies', function (Blueprint $table) {
            $table->string('parent_position_code')->nullable(true);
            $table->integer('level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('career_path_hierarchies', function (Blueprint $table) {
            $table->dropColumn('parent_position_code')->nullable(true);
        });

        Schema::table('career_path_hierarchies', function (Blueprint $table) {
            $table->string('parent_position_code')->nullable(true);
            $table->string('status_of_parent')->nullable(true);
            $table->dropColumn('level');
        });
    }
}
