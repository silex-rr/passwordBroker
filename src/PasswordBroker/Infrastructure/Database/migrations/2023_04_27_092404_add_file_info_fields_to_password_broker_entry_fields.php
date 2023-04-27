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
        Schema::table('password_broker_entry_fields', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('title');
            $table->bigInteger('file_size')->nullable()->after('file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_broker_entry_fields', function (Blueprint $table) {
            $table->dropColumn('file_name');
            $table->dropColumn('file_size');
        });
    }
};
