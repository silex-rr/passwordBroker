<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\TOTP;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('password_broker_entry_fields', function (Blueprint $table) {
            $table->integer('totp_timeout')
                ->default(null)
                ->nullable()
                ->after('login');
            $table->enum(
                'totp_hash_algorithm',
                [
                    TOTPHashAlgorithm::SHA1->value,
                    TOTPHashAlgorithm::SHA256->value,
                    TOTPHashAlgorithm::SHA512->value,
                ]
            )->nullable()
                ->default(null)
                ->after('totp_timeout');
        });
        DB::table('password_broker_entry_fields')
            ->where('type', '=', TOTP::TYPE)
            ->update([
                'totp_hash_algorithm' => TOTPHashAlgorithm::SHA1->value,
                'totp_timeout' => 30,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_broker_entry_fields', function (Blueprint $table) {
            $table->dropColumn('totp_timeout');
            $table->dropColumn('totp_hash_algorithm');
        });
    }
};
