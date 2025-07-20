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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['passenger', 'sender']);
            $table->enum('path_type', ['land', 'sea', 'air'])->nullable();
            $table->string('image', 2048)->nullable();
            $table->string('dimensions', 255)->nullable();
            $table->string('reason', 255)->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('weight');
            $table->tinyInteger('active')->default(0);
            $table->enum('status',['pending', 'approved', 'in_progress', 'completed', 'canceled', 'failed', 'reject'])->default('pending'); // pending, completed, failed
            $table->tinyInteger('vip')->default(0);
            $table->tinyInteger('priority')->default(0);
            $table->date('send_date')->nullable();
            $table->date('receive_date')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('o_country_id')->unsigned()->index();
            $table->foreign('o_country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('o_province_id')->unsigned()->index();
            $table->foreign('o_province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->bigInteger('o_city_id')->unsigned()->index();
            $table->foreign('o_city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->bigInteger('d_country_id')->unsigned()->index();
            $table->foreign('d_country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('d_province_id')->unsigned()->index();
            $table->foreign('d_province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->bigInteger('d_city_id')->unsigned()->index();
            $table->foreign('d_city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->text('address')->nullable();
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
        Schema::dropIfExists('projects');
    }
};