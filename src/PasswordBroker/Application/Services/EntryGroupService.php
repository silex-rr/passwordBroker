<?php

namespace PasswordBroker\Application\Services;

use Identity\Application\Services\RsaService;
use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EntryGroupService
{
    private EventDispatcher $dispatcher;
    private EncryptionService $encryptionService;
    private RsaService $rsaService;
    public function __construct(EventDispatcher $dispatcher, EncryptionService $encryptionService, RsaService $rsaService)
    {
        $this->dispatcher = $dispatcher;
        $this->encryptionService = $encryptionService;
        $this->rsaService = $rsaService;
    }
    public function addUserToGroupAsAdmin(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addAdmin($user, $encrypted_aes_password);
    }
    public function addUserToGroupAsModerator(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addModerator($user, $encrypted_aes_password);
    }
    public function addUserToGroupAsMember(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addMember($user, $encrypted_aes_password);
    }

    /**
     * @param string|null $encrypted_aes_password
     * @param string|null $master_password
     * @param EntryGroup $entryGroup
     * @param User $user
     * @return string
     */
    private function getEncryptedAesPassword(?string $encrypted_aes_password, ?string $master_password, EntryGroup $entryGroup, User $user): string
    {
        if ($master_password === '') {
            $master_password = null;
        }
        if (is_null($encrypted_aes_password)
            && !is_null($master_password)
        ) {
            /**
             * @var User $auth_user
             */
            $auth_user = Auth::user();
            $privateKey = $this->rsaService->getUserPrivateKey($auth_user->user_id, $master_password);
            $auth_encrypted_aes_password = $entryGroup->admins()->where('user_id', $auth_user->user_id)->firstOrFail()->encrypted_aes_password->getValue();
            $decrypted_aes_password = $privateKey->decrypt($auth_encrypted_aes_password);
            $publicKey = $this->rsaService->getUserPublicKey($user);
            $encrypted_aes_password = $publicKey->encrypt($decrypted_aes_password);
        }
        if (is_null($encrypted_aes_password)
            && $entryGroup->admins()->count() === 0
        ) {
            $password = $this->encryptionService->generatePassword();
            $publicKey = $this->rsaService->getUserPublicKey($user);
            $encrypted_aes_password = $publicKey->encrypt($password);
        }

        if (!is_string($encrypted_aes_password)) {
            throw new RuntimeException('Unable to get encrypted AES password for this Group');
        }
        return $encrypted_aes_password;
    }

    public function getDecryptedAesPassword(string $master_password, EntryGroup $entryGroup): string
    {
        /**
         * @var User $auth_user
         */
        $auth_user = Auth::user();
        $privateKey = $this->rsaService->getUserPrivateKey($auth_user->user_id, $master_password);
        $auth_encrypted_aes_password = $entryGroup->users()->where('user_id', $auth_user->user_id->getValue())
            ->firstOrFail()->encrypted_aes_password->getValue();
        return $privateKey->decrypt($auth_encrypted_aes_password);
    }
}
