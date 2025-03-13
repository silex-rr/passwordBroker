<?php

namespace PasswordBroker\Application\Services;

use Illuminate\Support\Facades\DB;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

readonly class EntryService
{
    public function __construct(
        private EntryGroupService $entryGroupService,
        private EncryptionService $encryptionService,
    ) {
    }

    public function moveEntryToAnotherGroup(Entry $entry, EntryGroup $entryGroupTarget, string $master_password): void
    {
        DB::transaction(fn() => $this->_moveEntryToAnotherGroup($entry, $entryGroupTarget, $master_password));
    }

    private function _moveEntryToAnotherGroup(Entry $entry, EntryGroup $entryGroupTarget, string $master_password): void
    {
        /**
         * @var EntryGroup $entryGroupSource
         */
        $entryGroupSource = $entry->entryGroup()->firstOrFail();

        $decryptedAesPasswordFrom = $this->entryGroupService->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup     : $entryGroupSource
        );

        $decryptedAesPasswordTarget = $this->entryGroupService->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup     : $entryGroupTarget
        );

        $entry->entryGroup()->associate($entryGroupTarget);

        /**
         * @var Field $field
         */
        foreach ($entry->fields()->all() as $field) {
            /**
             * @var Field $field
             */
            $data_decrypted = $this->encryptionService->decrypt(
                data_encrypted        : $field->value_encrypted->getValue(),
                decrypted_aes_password: $decryptedAesPasswordFrom,
                iv                    : $field->initialization_vector->getValue()
            );

            $field->value_encrypted = new ValueEncrypted(
                $this->encryptionService->encrypt(
                    data: $data_decrypted,
                    pass: $decryptedAesPasswordTarget,
                    iv  : $field->initialization_vector->getValue()
                )
            );

            $field->save();

            /**
             * @var EntryFieldHistory $fieldHistory
             */
            foreach ($field->fieldHistories()->get() as $fieldHistory) {
                $data_decrypted = $this->encryptionService->decrypt(
                    data_encrypted        : $fieldHistory->value_encrypted->getValue(),
                    decrypted_aes_password: $decryptedAesPasswordFrom,
                    iv                    : $fieldHistory->initialization_vector->getValue()
                );

                $fieldHistory->value_encrypted = new ValueEncrypted(
                    $this->encryptionService->encrypt(
                        data: $data_decrypted,
                        pass: $decryptedAesPasswordTarget,
                        iv  : $fieldHistory->initialization_vector->getValue()
                    )
                );


                $fieldHistory->save();
            };
        }

        $entry->save();
    }
}
