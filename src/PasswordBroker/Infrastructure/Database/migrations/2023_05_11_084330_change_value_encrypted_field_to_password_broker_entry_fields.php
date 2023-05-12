<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            //
        });

        if (DB::connection()->getConfig()['driver'] === 'mysql') {
            // Important for work with large encrypted file
            DB::statement(
                'ALTER TABLE `password_broker_entry_fields` MODIFY `value_encrypted` LONGBLOB'
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_broker_entry_fields', function (Blueprint $table) {
            //
        });
        if (DB::connection()->getConfig()['driver'] === 'mysql') {
            DB::statement(
                'ALTER TABLE `password_broker_entry_fields` MODIFY `value_encrypted` BLOB'
            );
        }
    }
};
