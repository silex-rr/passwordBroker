<?php

namespace Identity\Domain\User\Services;

use Identity\Application\Mail\RecoveryLinkEmail;
use Identity\Domain\User\Models\RecoveryLink;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
        Mail::to($this->recoveryLink->user()->first()->email->getValue())
            ->send(new RecoveryLinkEmail($this->recoveryLink));
    }
}
