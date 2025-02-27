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
        Schema::create('comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->text('title')->nullable();
            $table->string('img', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->text('message');
            $table->tinyInteger('rating')->nullable();
            $table->dateTime('timestamp');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');

            // Khóa ngoại
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};