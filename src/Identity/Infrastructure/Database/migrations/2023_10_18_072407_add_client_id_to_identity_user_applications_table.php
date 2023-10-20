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
        Schema::table('identity_user_applications', function (Blueprint $table) {
            $table->uuid('client_id')->after('user_id');
            $table->unique(['user_id', 'client_id'], 'u_user_id_client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identity_user_applications', function (Blueprint $table) {
            $table->dropUnique('u_user_id_client_id');
            $table->dropColumn('client_id');
        });
    }
};
