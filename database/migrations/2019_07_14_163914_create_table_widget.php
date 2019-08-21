<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWidget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('name');
            $table->text('description');
            $table->enum('app_code', ['DESKTOP', 'ESS', 'MOBILE'])->nullable();
            $table->uuid('widget_type_id');
            $table->text('param_in');
            $table->text('param_out');

            $table->primary(['id']);
            $table->unique('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget');
    }
}
