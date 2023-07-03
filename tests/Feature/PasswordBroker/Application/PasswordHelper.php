<?php
namespace Tests\Feature\PasswordBroker\Application;

use Identity\Application\Services\RsaService;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

trait PasswordHelper
{
    /**
     * @param User $admin
     * @param EntryGroup $entryGroup
     * @param Entry $entry
     * @param string $password_str
     * @return Password
     */
    public function getPasswordHelper(User $admin, EntryGroup $entryGroup, Entry $entry, string $password_str): Password
    {
        /**
         * @var EncryptionService $encryptionService
         */
        $encryptionService = app(EncryptionService::class);
        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);
        $iv = $encryptionService->generateInitializationVector();
        $encrypted_aes_password = $admin->userOf()->where('entry_group_id', $entryGroup->entry_group_id)->firstOrFail()->encrypted_aes_password;
        $privateKey = $rsaService->getUserPrivateKey($admin->user_id, UserFactory::MASTER_PASSWORD);
        $decrypted_aes_password = $privateKey->decrypt($encrypted_aes_password);

        $password_str_encrypted = $encryptionService->encrypt($password_str, $decrypted_aes_password, $iv);

        $password = $entry->addPassword(
            userId: $admin->user_id,
            password_encrypted: $password_str_encrypted,
            initializing_vector: $iv,
            login: 'test_login'
        );

        $this->assertCount(1,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
        return $password;
    }
}