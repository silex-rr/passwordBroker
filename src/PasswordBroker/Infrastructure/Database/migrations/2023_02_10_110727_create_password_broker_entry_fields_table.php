<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('password_broker_entry_fields', static function (Blueprint $table) {
            $table->uuid('field_id')->primary();
            $table->foreignUuid('entry_id')
                ->references('entry_id')
                ->on('password_broker_entries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('type');
            $table->string('title');
            $table->binary('value_encrypted');
            $table->binary('initialization_vector');
            $table->foreignUuid('created_by')
                ->nullable()
                ->references('user_id')
                ->on('identity_users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignUuid('update_by')
                ->nullable()
                ->references('user_id')
                ->on('identity_users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('password_broker_entry_fields');
    }
};
