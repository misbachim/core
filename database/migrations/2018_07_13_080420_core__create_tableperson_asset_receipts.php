<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreCreateTablepersonAssetReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_asset_receipts', function (Blueprint $table) {
          $table->integer('tenant_id')->nullable(true);
          $table->integer('company_id')->nullable(true);
          $table->increments('id');
          $table->integer('person_id')->nullable(true);
          $table->char('type', 1)->nullable(true);
          $table->date('date')->nullable(true);
          $table->string('file_receipt', 1000)->nullable(true);
          $table->integer('created_by')->nullable(true);
          $table->timestampTz('created_at')->nullable(true);
          $table->integer('updated_by')->nullable(true);
          $table->timestampTz('updated_at')->nullable(true);
          $table->string('receipt_number', 50)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_asset_receipts');
    }
}
