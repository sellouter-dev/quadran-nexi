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
        Schema::create('seller_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_unique_id')->index()->nullable();
            $table->foreign('customer_unique_id')->references('unique_id')->on('customers')->onDelete('cascade');
            $table->unsignedBigInteger('marketplace_id')->index()->nullable();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces');
            $table->date("date");
            $table->string('fnsku', 50)->nullable();
            $table->string('asin', 50)->index();
            $table->string('msku', 50)->nullable();
            $table->longText('title')->nullable();
            $table->string('disposition', 50)->index()->nullable();
            $table->integer('starting_warehouse_balance')->nullable();
            $table->integer('in_transit_between_warehouses')->nullable();
            $table->integer('receipts')->nullable();
            $table->integer('customer_shipments')->nullable();
            $table->integer('customer_returns')->nullable();
            $table->integer('vendor_returns')->nullable();
            $table->integer('warehouse_transfer_in_out')->nullable();
            $table->integer('found')->nullable();
            $table->integer('lost')->nullable();
            $table->integer('damaged')->nullable();
            $table->integer('disposed')->nullable();
            $table->integer('other_events')->nullable();
            $table->integer('ending_warehouse_balance')->nullable();
            $table->integer('unknown_events')->nullable();
            $table->string('location', 50)->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_inventory_items');
    }
};
