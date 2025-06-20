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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('priod', ['monthly', 'yearly'])->default('monthly');
            $table->tinyInteger('status')->default(0);
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('period_count')->default(1);
            $table->unsignedInteger('claim_count')->default(0);
            $table->unsignedInteger('project_count')->default(0);
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
        Schema::dropIfExists('plans');
    }
};