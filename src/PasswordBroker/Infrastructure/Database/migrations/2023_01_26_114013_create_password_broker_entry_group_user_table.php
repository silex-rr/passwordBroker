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
        Schema::create('password_broker_entry_group_user', static function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')
                ->references('user_id')
                ->on('identity_users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('entry_group_id')
                ->references('entry_group_id')
                ->on('password_broker_entry_groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('role');
            $table->timestamps();

            $table->unique(['user_id', 'entry_group_id'], 'unique_user_to_entry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('password_broker_entry_group_user');
    }
};
