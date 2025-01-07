<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('data_collections', function (Blueprint $table) {
            $table->id();
            $table->date('deposit_date')->nullable();
            $table->date('document_date')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('document_number')->nullable();
            $table->string('document_type')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('currency')->nullable();
            $table->float('amount')->nullable();
            $table->string('unique_code_rif3')->nullable();
            $table->string('buyer_tax_registration_type')->nullable();
            $table->string('buyer_vat_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_collections');
    }
};