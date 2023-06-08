<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\FieldWasUpdated;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\InitializationVector;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

class UpdateField implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(
        protected User       $user,
        protected Entry      $entry,
        protected EntryGroup $entryGroup,
        protected Field      $field,
        protected ?string    $title,
        protected ?string    $value_encrypted,
        protected ?string    $initialization_vector,
        protected ?string    $login,
        protected ?string    $value,
        protected ?string    $master_password
    )
    {
    }

    public function handle(): void
    {
        $this->validate();

        if (!is_null($this->value)) {
            /**
             * @var EntryGroupService $entryGroupService
             */
            $entryGroupService = app(EntryGroupService::class);
            $decryptedAesPassword = $entryGroupService->getDecryptedAesPassword($this->master_password, $this->entryGroup);
            /**
             * @var EncryptionService $encryptionService
             */
            $encryptionService = app(EncryptionService::class);
            $this->initialization_vector = $encryptionService->generateInitializationVector();
            $this->value_encrypted = $encryptionService->encrypt($this->value, $decryptedAesPassword, $this->initialization_vector);
        }

        $fields_for_update = [];

        if (!is_null($this->value_encrypted)) {
            $fields_for_update['value_encrypted'] = ValueEncrypted::fromNative($this->value_encrypted);
            $fields_for_update['initialization_vector'] = InitializationVector::fromNative($this->initialization_vector);
        }
        if (!is_null($this->title)
           && $this->field->title->getValue() !== $this->title
        ) {
            $fields_for_update['title'] = Title::fromNative($this->title);
        }
        if ($this->field->getType() === Password::TYPE
            && $this->field->login->getValue() !== $this->login
        ) {
            $fields_for_update['login'] = Login::fromNative($this->login);
        }

        if (!empty($fields_for_update)) {
            $fields_for_update['updated_by'] = $this->user->user_id;
            $this->field->update($fields_for_update);
            event(new FieldWasUpdated($this->entry, $this->field));
        }

    }

    public function validate(): void
    {
        if ((int)is_null($this->value) + (int)is_null($this->master_password) === 1) {
            throw new InvalidArgumentException('For updating the field value by insecure way both the master password and the value should be provided');
        }
        if ((int)is_null($this->value_encrypted) + (int)is_null($this->initialization_vector) === 1) {
            throw new InvalidArgumentException('For updating the field value by secure way both the encrypted value and the initialization vector should be provided');
        }
    }
}
