<?php

namespace Identity\Domain\User\Services;

use Exception;
use Identity\Application\Mail\RecoveryLinkEmail;
use Identity\Domain\User\Models\RecoveryLink;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Monolog\Logger;

class SendLetterWithRecoveryLink implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly RecoveryLink $recoveryLink,
    )
    {
    }

    public function handle(): void
    {
        $log = new Logger('send-letter-with-recovery-link');
        $email = $this->recoveryLink->user()->first()->email->getValue();
        try {
            Mail::to($email)
                ->send(new RecoveryLinkEmail($this->recoveryLink));
            $log->info("Letter with recovery link sent to {$email}");
        } catch (Exception $exception) {
            $log->error($exception->getMessage());
        }
    }
}
