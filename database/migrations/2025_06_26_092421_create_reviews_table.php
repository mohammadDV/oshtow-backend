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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->tinyInteger('rate');
            $table->tinyInteger('status')->default(0);
            $table->bigInteger("claim_id")->unsigned()->index();
            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            $table->bigInteger("owner_id")->unsigned()->index();
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger("user_id")->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};