<?php

namespace PasswordBroker\Application\Services;

use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use Symfony\Component\EventDispatcher\EventDispatcher;

readonly class EntryService
{
    public function __construct(
        private EventDispatcher   $dispatcher,
        private EntryGroupService $entryGroupService,
        private EncryptionService $encryptionService
    )
    {
    }

    public function moveEntryToAnotherGroup(Entry $entry, EntryGroup $entryGroupTarget, string $master_password): void
    {
        /**
         * @var EntryGroup $entryGroupSource
         */
        $entryGroupSource = $entry->entryGroup()->firstOrFail();

        $decryptedAesPasswordFrom = $this->entryGroupService->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup: $entryGroupSource
        );

        $decryptedAesPasswordTarget = $this->entryGroupService->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup: $entryGroupTarget
        );

        $entry->entryGroup()->associate($entryGroupTarget);

        foreach ($entry->fields()->all() as $field) {
            /**
             * @var Field $field
             */
            $data_decrypted = $this->encryptionService->decrypt(
                data_encrypted: $field->value_encrypted->getValue(),
                decrypted_aes_password: $decryptedAesPasswordFrom,
                iv: $field->initialization_vector->getValue()
            );

            $field->value_encrypted = new ValueEncrypted(
                $this->encryptionService->encrypt(
                    data: $data_decrypted,
                    pass: $decryptedAesPasswordTarget,
                    iv: $field->initialization_vector->getValue()
                )
            );

            $field->save();
        }

        $entry->save();
    }
}
