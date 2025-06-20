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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedInteger('weight');
            $table->text('address')->nullable();
            $table->enum('address_type', ['me', 'other'])->default('me');
            $table->string('confirmation_image', 2048)->nullable();
            $table->string('image', 2048)->nullable();
            $table->text('confirmation_description')->nullable();
            $table->string('delivery_code', 50)->nullable();
            $table->string('confirmed_code', 50)->nullable();
            $table->enum('status',['pending', 'approved', 'paid', 'in_progress', 'delivered', 'canceled'])->default('pending'); // pending, completed, failed
            $table->bigInteger("project_id")->unsigned()->index();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->bigInteger("user_id")->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger("sponsor_id")->unsigned()->index();
            $table->foreign('sponsor_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};