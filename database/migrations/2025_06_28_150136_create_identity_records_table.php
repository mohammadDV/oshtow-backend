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
        Schema::create('identity_records', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('national_code');
            $table->string('mobile');
            $table->date('birthday');
            $table->string('email');
            $table->string('country');
            $table->string('postal_code');
            $table->string('address');
            $table->string('image_national_code_front');
            $table->string('image_national_code_back');
            $table->string('video');
            $table->enum('status',['pending', 'paid', 'completed'])->default('pending'); // pending, completed, failed
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
        Schema::dropIfExists('identity_records');
    }
};