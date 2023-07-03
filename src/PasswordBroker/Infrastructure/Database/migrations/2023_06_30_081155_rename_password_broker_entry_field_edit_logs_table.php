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
        Schema::table('password_broker_entry_field_edit_logs', function (Blueprint $table) {
            $table->rename('password_broker_entry_field_history');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_broker_entry_field_history', function (Blueprint $table) {
            $table->rename('password_broker_entry_field_edit_logs');
        });
    }
};
