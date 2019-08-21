<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueKeyToLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
          $table->dropPrimary('locations_pkey');
          $table->primary('id');
          $table->unique(array('tenant_id','company_id','code'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
          $table->dropPrimary('locations_pkey');
          $table->dropUnique('locations_tenant_id_company_id_code_unique');
          $table->primary('code');
        });
    }
}
