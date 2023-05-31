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
    public function up()
    {
        Schema::create('password_broker_entry_field_edit_logs', static function (Blueprint $table) {
            $table->uuid('field_edit_log_id')->primary();
            $table->foreignUuid('field_id')
                ->references('field_id')
                ->on('password_broker_entry_fields')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('title');
            $table->string('type');
            $table->binary('value_encrypted');
            $table->boolean('is_deleted');
            $table->foreignUuid('updated_by')
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
    public function down()
    {
        Schema::dropIfExists('password_broker_entry_fields_edit_logs');
    }
};
