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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->datetime('order_date')->notNull();
            $table->decimal('total_price', 10, 0)->notNull();
            $table->string('receiver_name', 100)->nullable();
            $table->string('receiver_address', 100)->nullable();
            $table->tinyInteger('status')->notNull();
            
            // Khóa ngoại
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('payment_method_id')->notNull();
            $table->unsignedBigInteger('shipping_method_id')->notNull();
            
            // Ràng buộc khóa ngoại
            $table->foreign('voucher_id')->references('voucher_id')->on('vouchers')->nullOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('payment_method_id')->on('payment_methods')->restrictOnDelete();
            $table->foreign('shipping_method_id')->references('shipping_method_id')->on('shipping_methods')->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};