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
        Schema::table('users', function (Blueprint $table) {
            // Add composite indexes for commonly filtered fields
            $table->index(['status', 'level'], 'users_status_level_index');
            $table->index(['created_at', 'status'], 'users_created_at_status_index');

            // Add indexes for searchable fields
            $table->index('email');
            $table->index('mobile');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('nickname');

            // Add index for sorting
            $table->index('created_at');

            // Add index for verification status
            $table->index('email_verified_at');
            $table->index('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_level_index');
            $table->dropIndex('users_created_at_status_index');
            $table->dropIndex(['email']);
            $table->dropIndex(['mobile']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['nickname']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['email_verified_at']);
            $table->dropIndex(['verified_at']);
        });
    }
};
