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
        Schema::create('claim_steps', function (Blueprint $table) {
            $table->bigInteger("step_id")->unsigned()->index();
            $table->bigInteger("claim_id")->unsigned()->index();
            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            $table->string('data', 2048)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->primary(['claim_id', 'step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_steps');
    }
};