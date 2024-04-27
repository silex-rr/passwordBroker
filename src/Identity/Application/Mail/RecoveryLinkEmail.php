<?php

namespace Identity\Application\Mail;

use Identity\Application\Services\RecoveryUserService;
use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Models\RecoveryLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class RecoveryLinkEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly RecoveryLink $recoveryLink,
    )
    {
        $type = match ($this->recoveryLink->type) {
            RecoveryLinkType::INVITE => 'Invite',
            default => 'Recovery'
        };

        $this->subject($type . ' Link for ' . env('APP_NAME'));
    }
    public function content(): Content
    {
        $view = match ($this->recoveryLink->type) {
            RecoveryLinkType::INVITE => 'recovery',
            default => 'invite'
        };

        /**
         * @var RecoveryUserService $recoveryUserService
         */
        $recoveryUserService = app(RecoveryUserService::class);

        $content = new Content(view: 'identity.mails.' . $view);
        $content->with([
            'user' => $this->recoveryLink->user()->first(),
            'expired_at' => $this->recoveryLink->expired_at,
            'activation_link_url' => $recoveryUserService->makeRecoveryUrl($this->recoveryLink),
            'APP_NAME' => env('APP_NAME'),
        ]);

        return $content;
    }
}
