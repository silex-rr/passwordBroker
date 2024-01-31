<?php

namespace System\Application\Listners;

use Illuminate\Contracts\Bus\Dispatcher;
use System\Domain\Backup\Events\BackupWasMade;
use System\Domain\Backup\Service\SendLetterAboutMadeBackup;
use System\Domain\Settings\Models\BackupSetting;

class BackupWasMadeListener
{

    public function handle(BackupWasMade $backupWasMade): void
    {
        /**
         * @var BackupSetting $backupSetting
         */
        $backupSetting = BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]);
        if (!$backupSetting->getEmailEnable()->getValue()
            && empty($backupSetting->getEmail()->getValue())
        ){
            return;
        }


        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);
        $dispatcher->dispatch(new SendLetterAboutMadeBackup(
            backup: $backupWasMade->backup,
            backupSetting: $backupSetting,
        ));
    }
}
