<?php

namespace Identity\Application\Services;

use Carbon\Carbon;
use Identity\Domain\User\Models\Attributes\RecoveryLinkKey;
use Identity\Domain\User\Models\Attributes\RecoveryLinkStatus;
use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\ChangePasswordForUser;
use Identity\Domain\User\Services\UpdateUser;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Auth;
use JsonException;
use PasswordBroker\Infrastructure\Services\PasswordGenerator;
use RuntimeException;

readonly class RecoveryUserService
{
    public const int LINK_EXPIRATIONS_HOURS = 24;

    public function __construct(
        private FingerprintService $fingerprintService,
        private PasswordGenerator  $passwordGenerator,
        private Dispatcher         $dispatcher,
    )
    {
    }

    public function createRecoveryLink(User $user, RecoveryLinkType $linkType, string $fingerprintFront = null, User $issuedByUser = null): RecoveryLink
    {
        $fingerprintFrontArray = [];
        if ($fingerprintFront) {
            try {
                $fingerprintFrontArray = json_decode($fingerprintFront, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
            }
        }

        $this->passwordGenerator->setLength(42);

        $recoveryLink = new RecoveryLink();
        $recoveryLink->user()->associate($user);
        if ($issuedByUser) {
            $recoveryLink->issuedByUser()->associate($issuedByUser);
        }
        $recoveryLink->key = RecoveryLinkKey::fromNative($this->passwordGenerator->generate());
        $recoveryLink->type = $linkType;
        $recoveryLink->status = RecoveryLinkStatus::default();
        $recoveryLink->created_by_fingerprint = $this->fingerprintService->makeFingerprint($fingerprintFrontArray);
        $recoveryLink->expired_at = (new Carbon())->add('hour', self::LINK_EXPIRATIONS_HOURS);

        return $recoveryLink;
    }

    public function activateRecoveryLink(
        RecoveryLink $recoveryLink,
        string       $password,
        ?string      $username = null,
        ?string      $master_password = null,
    ): void
    {
        if ($recoveryLink->status !== RecoveryLinkStatus::AWAIT) {
            throw new RuntimeException('Link is '
                . ($recoveryLink->status === RecoveryLinkStatus::OUTDATED ? 'outdated' : 'used'));
        }
        if ($recoveryLink->expired_at < Carbon::now()) {
            throw new RuntimeException('Link has expired');
        }

        $update = RecoveryLink::query()->where('recovery_link_id', $recoveryLink->recovery_link_id->getValue())
            ->update(['status' => RecoveryLinkStatus::IN_PROCESS]);

        if ($update !== 1) {
            throw new RuntimeException("Link has been already activated");
        }

        $recoveryLink->refresh();

        /**
         * @var User $user
         */
        $user = $recoveryLink->user()->firstOrFail();

        Auth::login($user);

        switch ($recoveryLink->type) {
            case RecoveryLinkType::RECOVERY:
                $this->dispatcher->dispatchSync(new ChangePasswordForUser(userTarget: $user, newPassword: $password));
                break;
            case RecoveryLinkType::INVITE:
                $this->dispatcher->dispatchSync(new UpdateUser(
                    userTarget: $user,
                    username: $username,
                    password: $password,
                ));
                break;
            default:
                throw new RuntimeException('Unexpected Link Type');
        }


    }

    public function makeRecoveryUrl(RecoveryLink $recoveryLink): string
    {
        $route = route('recovery_landing', $recoveryLink);
        if ($_ENV['APP_URL_FRONT']) {
            $route = str_replace($_ENV['APP_URL'], $_ENV['APP_URL_FRONT'], $route);
        }

        return $route;
    }
}
