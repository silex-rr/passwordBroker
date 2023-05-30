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
        Schema::table('password_broker_entry_groups', function (Blueprint $table) {
            $table->text('materialized_path')->after('parent_entry_group_id');
        });
        /**
         * @var \PasswordBroker\Application\Services\EntryGroupService $entryGroupService
         */
        $entryGroupService = app(\PasswordBroker\Application\Services\EntryGroupService::class);
        $entryGroups = \PasswordBroker\Domain\Entry\Models\EntryGroup::whereNull('parent_entry_group_id');
        foreach ($entryGroups->get() as $entryGroup) {
            $entryGroupService->rebuildMaterializedPath($entryGroup);
        }
//        DB::connection()->setTablePrefix('');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_broker_entry_groups', function (Blueprint $table) {
            $table->dropColumn('materialized_path');
        });
    }
};
