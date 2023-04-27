<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\FieldWasAddedToEntry;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use InvalidArgumentException;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Link;
use PasswordBroker\Domain\Entry\Models\Fields\Note;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use RuntimeException;

class AddFieldToEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    //, SerializesModels;
    public function __construct(
        protected Entry         $entry,
        protected EntryGroup    $entryGroup,
        protected string        $type,
        protected ?string       $title,
        protected ?string       $value_encrypted,
        protected ?string       $initialization_vector,
        protected ?string       $value,
        protected ?UploadedFile $file,
        protected ?string       $master_password
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
            if ($this->value) {
                $this->value_encrypted = $encryptionService->encrypt($this->value, $decryptedAesPassword, $this->initialization_vector);
            } elseif ($this->file) {
                $this->value_encrypted = $encryptionService->encrypt($this->file->getContent(), $decryptedAesPassword, $this->initialization_vector);
            }
        }

        $method = 'add' . ucfirst($this->type);

        if (!method_exists($this->entry, $method)){
            throw new RuntimeException('Method ' . $method . ' does not exist in ' . $this->entry::class);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();
        /**
         * @var Field|null $field
         */
        $field = null;
        switch ($this->type) {
            default: break;
            case File::TYPE:
                $field = $this->entry->addFile(
                    userId: $user->user_id,
                    file_encrypted: $this->value_encrypted,
                    initializing_vector: $this->initialization_vector,
                    title: $this->title ?: '',
                    file_size: (int)$this->file->getSize(),
                    file_name: $this->file->getFilename()
                );
                break;
            case Password::TYPE:
            case Link::TYPE:
            case Note::TYPE:
                $field = $this->entry->$method(
                    $user->user_id,
                    $this->value_encrypted,
                    $this->initialization_vector,
                    $this->title ?: '',
                );
                break;
        }


        event(new FieldWasAddedToEntry($this->entry, $field));
    }

    public function validate(): void
    {
        if (is_null($this->value_encrypted)
            && ((is_null($this->value) && is_null($this->file)) || is_null($this->master_password))
        ) {
            throw new InvalidArgumentException('Field can be added only if the master password and the value or the file are provided');
        }
        if (is_null($this->value)
            && is_null($this->file)
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
