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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('name', 50); // varchar 
            $table->text('description')->nullable();
            $table->string('image', 255);
            $table->string('image_detail1', 255)->nullable();
            $table->string('image_detail2', 255)->nullable();
            $table->string('image_detail3', 255)->nullable();
            $table->integer('stock_quantity');
            $table->decimal('price', 10, 0);
            $table->decimal('price_sale', 10, 0)->nullable();
            $table->string('gender', 50);
            $table->string('color', 50);
            $table->unsignedBigInteger('category_id')-> nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->timestamps();

            // FK
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null');
            $table->foreign('promotion_id')->references('promotion_id')->on('promotions')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};