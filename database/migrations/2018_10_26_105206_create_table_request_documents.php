<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_documents', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->increments('id');
            $table->char('crud_type', 1);
            $table->integer('person_id');
            $table->string('employee_id', 50);
            $table->integer('person_document_id')->nullable(true);
            $table->string('lov_dcty', 10)->nullable(true);
            $table->string('name', 50)->nullable(true);
            $table->date('expired')->nullable(true);
            $table->string('file_document', 1000)->nullable(true);
            $table->char('status', 1);
            $table->date('request_date')->nullable(true);
            $table->timestampTz('created_at');
            $table->integer('created_by');
            $table->timestampTz('updated_at')->nullable(true);
            $table->integer('updated_by')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_documents');
    }
}
