<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdToPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('positions', 'id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->integer('id')->nullable();
            });

            Schema::table('positions', function (Blueprint $table) {
                $positions = DB::table('positions')
                    ->select('code')
                    ->get();

                $count = 1;
                foreach ($positions as $posts) {
                    DB::table('positions')
                        ->where(
                            'code', $posts->code
                        )
                        ->update(['id' => $count++]);
                }
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
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'id')) {
                $table->dropColumn('id');
            }
        });
    }
}
