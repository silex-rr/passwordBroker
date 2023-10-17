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
        Schema::create('identity_user_applications', function (Blueprint $table) {
            $table->uuid('user_application_id')->primary();
            $table->foreignUuid('user_id')->references('user_id')
                ->on('identity_users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('is_offline_database_mode')->default(false);
            $table->boolean('is_offline_database_required_update')->default(false);
            $table->boolean('is_rsa_private_required_update')->default(false);
            $table->timestamp('offline_database_fetched_at')->nullable();
            $table->timestamp('rsa_private_fetched_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('identity_user_applications');
    }
};
