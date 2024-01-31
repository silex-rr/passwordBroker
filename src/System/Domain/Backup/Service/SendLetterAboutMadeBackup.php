<?php

namespace System\Domain\Backup\Service;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use System\Application\Mail\BackupEmail;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Backup;
use System\Domain\Settings\Models\BackupSetting;

class SendLetterAboutMadeBackup implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly Backup        $backup,
        private readonly BackupSetting $backupSetting,
    )
    {}
    public function handle(): void
    {
        if ($this->backup->state !== BackupState::CREATED) {
            return;
        }

        Mail::to($this->backupSetting->getEmail()->getValue())
            ->send(new BackupEmail($this->backup));
    }
}
