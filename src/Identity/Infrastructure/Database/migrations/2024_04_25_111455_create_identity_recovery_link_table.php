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
        Schema::create('identity_recovery_links', function (Blueprint $table) {
            $table->uuid('recovery_link_id')->primary();
            $table->foreignUuid('user_id')->references('user_id')
                ->on('identity_users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('issued_by_user_id')
                ->nullable()
                ->references('user_id')
                ->on('identity_users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('key');
            $table->string('type');
            $table->string('status');
            $table->timestamp('expired_at');
            $table->timestamp('activated_at')->nullable();
            $table->json('created_by_fingerprint')->nullable();
            $table->json('activated_by_fingerprint')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_recovery_link');
    }
};
