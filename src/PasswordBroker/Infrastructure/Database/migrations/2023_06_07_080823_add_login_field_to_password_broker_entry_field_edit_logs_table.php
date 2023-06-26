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
            $table->string('login')->nullable()->after('type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_broker_entry_field_edit_logs', function (Blueprint $table) {
            $table->dropColumn('login');
        });
    }
};
