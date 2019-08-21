<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestPersonDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_person_documents')) {
            Schema::create('request_person_documents', function (Blueprint $table) {
                $table->integer('tenant_id');
                $table->integer('profile_request_id');
                $table->increments('id');
                $table->char('crud_type', 1);
                $table->integer('person_document_id')->nullable(true);
                $table->string('lov_dcty', 10)->nullable(true);
                $table->string('name', 50)->nullable(true);
                $table->date('expired')->nullable(true);
                $table->string('file_document', 1000)->nullable(true);
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
        Schema::dropIfExists('request_person_documents');
    }
}
