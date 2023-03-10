<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\FieldWasAddedToEntry;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use InvalidArgumentException;

class AddFieldToEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    //, SerializesModels;
    public function __construct(
        protected Entry      $entry,
        protected EntryGroup $entryGroup,
        protected string     $type,
        protected ?string    $title,
        protected ?string    $value_encrypted,
        protected ?string    $initialization_vector,
        protected ?string    $value,
        protected ?string    $master_password
    )
    {
    }

    public function handle(): void
    {
        $this->validate();

        if (is_null($this->value_encrypted)) {
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

        $method = 'add' . ucfirst($this->type);

        /**
         * @var User $user
         */
        $user = Auth::user();
        /**
         * @var Field $field
         */
        $field = $this->entry->$method(
            $user->user_id,
            $this->value_encrypted,
            $this->initialization_vector,
            $this->title ?: ''
        );

        event(new FieldWasAddedToEntry($this->entry, $field));
    }

    public function validate(): void
    {
        if (is_null($this->value_encrypted)
            && (is_null($this->value) || is_null($this->master_password))
        ) {
            throw new InvalidArgumentException('Field can be added only if the master password and the value are provided');
        }
        if (is_null($this->value)
            && (is_null($this->value_encrypted) || is_null($this->initialization_vector))
        ) {
            throw new InvalidArgumentException('Field can be added only if the encrypted value and the initialization vector are provided');
        }
        $method = 'add' . ucfirst($this->type);
        if (!method_exists($this->entry, $method)) {
            throw new InvalidArgumentException('Method ' . $method . ' does not exists in Entry. Invalid Field Type was specified');
        }
    }
}
