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
        Schema::table('password_broker_entry_groups', static function (Blueprint $table) {
            $table->foreignUuid('parent_entry_group_id')
                ->nullable()
                ->after('entry_group_id')
                ->references('entry_group_id')
                ->on($table->getTable())
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('password_broker_entry_groups', static function (Blueprint $table) {
            $table->dropColumn('parent_group_id');
        });
    }
};
