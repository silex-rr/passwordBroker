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
        Schema::table('password_broker_entry_group_user', static function (Blueprint $table) {
            $table->binary('encrypted_aes_password')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('password_broker_entry_group_user', static function (Blueprint $table) {
            $table->dropColumn('encrypted_aes_password');
        });
    }
};
