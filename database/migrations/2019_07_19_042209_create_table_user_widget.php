<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserWidget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_widget', function (Blueprint $table) {
            $table->uuid('id');            
            $table->integer('user_id');
            $table->uuid('widget_id');
            $table->integer('x_position');
            $table->integer('y_position');
            $table->integer('widget_number');

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
        Schema::dropIfExists('user_widget');   
    }
}
