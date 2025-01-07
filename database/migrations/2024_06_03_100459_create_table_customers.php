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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('unique_id')->unique(); // Aggiungi il vincolo unico qui
            $table->string('company_name', 50)->index();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('email', 100)->unique();
            $table->string('phone')->nullable();
            $table->string('web_site', 50)->nullable();
            $table->enum('turnover_projection', ["<100k", "100k-1m", "1m-5m", ">5m"])->nullable();
            $table->string('address', 50)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('province', 10)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->string('fiscal_code', 50)->nullable();
            $table->boolean('amazon_seller')->default(false);
            $table->boolean('amazon_vendor')->default(false);
            $table->boolean('amazon_advertising')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
