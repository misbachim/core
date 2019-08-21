<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLinkWebsiteToEducationInstitution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('education_institutions', 'link_website')) {
            Schema::table('education_institutions', function (Blueprint $table) {
                $table->string('link_website', 255)->nullable(true);
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
        Schema::table('education_institutions', function (Blueprint $table) {
            if (Schema::hasColumn('education_institutions', 'link_website')) {
                $table->dropColumn('link_website');
            }
        });
    }
}
