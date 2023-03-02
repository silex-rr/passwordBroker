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
        Schema::create('password_broker_entries', static function (Blueprint $table) {
//            $table->id();
            $table->uuid('entry_id')->primary();
            $table->foreignUuid('entry_group_id')
                ->references('entry_group_id')
                ->on('password_broker_entry_groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('title');
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
        Schema::dropIfExists('password_broker_passwords');
    }
};
