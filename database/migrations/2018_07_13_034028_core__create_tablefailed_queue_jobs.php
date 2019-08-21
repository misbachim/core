<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablefailedQueueJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_queue_jobs', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->text('connection');
          $table->text('queue');
          $table->longText('payload');
          $table->longText('exception');
          $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_queue_jobs');
    }
}
