 <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUniqueIndexInTableJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            DB::statement('alter table "jobs" drop constraint "jobs_pkey" cascade');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->primary(array('tenant_id', 'company_id', 'code'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            DB::statement('alter table "jobs" drop constraint "jobs_pkey" cascade');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->primary('id');
        });
    }
}
