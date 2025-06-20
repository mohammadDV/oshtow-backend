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
        Schema::create('payment_secures', function (Blueprint $table) {
            $table->id();
            $table->integer('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->bigInteger("wallet_id")->unsigned()->index();
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->bigInteger("claim_id")->unsigned()->index();
            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'released', 'cancelled']); // pending, released, cancelled
            $table->timestamp('expires_at')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger("user_id")->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['wallet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_secures');
    }
};