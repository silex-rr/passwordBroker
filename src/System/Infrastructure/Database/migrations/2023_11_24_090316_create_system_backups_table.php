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
        Schema::create('system_backups', static function (Blueprint $table) {
            $table->uuid('backup_id')->primary();
            $table->string('file_name')->nullable()->unique();
            $table->string('state');
            $table->integer('size')->nullable();
            $table->timestamp('backup_created')->nullable();
            $table->timestamp('backup_deleted')->nullable();
            $table->text('error_message')->nullable();
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
        Schema::dropIfExists('system_backups');
    }
};
