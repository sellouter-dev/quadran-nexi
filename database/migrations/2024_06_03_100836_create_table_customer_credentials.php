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
        Schema::create('customer_credentials', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_unique_id')->index()->nullable();
            $table->foreign('customer_unique_id')->references('unique_id')->on('customers')->onDelete('cascade');
            $table->string('type', 20)->index();
            $table->unsignedBigInteger('marketplace_id')->index();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces');
            $table->string('token')->nullable();
            $table->string('lwa_client_id', 255);
            $table->longText('lwa_client_secret');
            $table->longText('lwa_refresh_token')->nullable();
            $table->string('access_key_id', 100);
            $table->longText('secret_access_key');
            $table->longText('role_arn');
            $table->string('profile_id', 100)->nullable();
            $table->string('dsp_profile_id', 100)->nullable();
            $table->string('region', 20);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_credentials');
    }
};
