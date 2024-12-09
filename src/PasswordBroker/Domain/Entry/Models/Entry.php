<?php

namespace PasswordBroker\Domain\Entry\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Attributes\UserId as UserIdAttribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\EntryCreated;
use PasswordBroker\Application\Events\EntryForceDeleted;
use PasswordBroker\Application\Events\EntryRestored;
use PasswordBroker\Application\Events\EntryTrashed;
use PasswordBroker\Application\Events\EntryUpdated;
use PasswordBroker\Domain\Entry\Models\Casts\EntryId;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileMime;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileName;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileSize;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\InitializationVector;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPTimeout;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\ValueEncrypted;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Link;
use PasswordBroker\Domain\Entry\Models\Fields\Note;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Models\Fields\TOTP;
use PasswordBroker\Infrastructure\Factories\Entry\EntryFactory;
use PasswordBroker\Infrastructure\Validation\EntryValidator;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;

/**
 * @property Attributes\EntryId $entry_id
 * @property Attributes\Title $title
 *
 * @method static EntryFactory factory
 */
#[Schema(
    schema: "PasswordBroker_Entry",
    properties: [
        new Property(property: "entry_id", ref: "#/components/schemas/PasswordBroker_EntryId"),
        new Property(property: "entry_group_id", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
        new Property(property: "title", ref: "#/components/schemas/PasswordBroker_Title"),
        new Property(property: "created_at", type: "string", format: "date-time", nullable: false,),
        new Property(property: "updated_at", type: "string", format: "date-time", nullable: true,),
        new Property(property: "deleted_at", type: "string", format: "date-time", nullable: true,),
    ],
    type: "object",
)]
class Entry extends Model
{
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    use SoftDeletes;
    protected $primaryKey = 'entry_id';
    public $incrementing = false;
    public $keyType = 'string';
    protected $guarded = ['entry_id'];
    protected $casts = [
        'entry_id' => EntryId::class,
        'title' => Casts\Title::class
    ];

    protected $dispatchesEvents = [
        'created' => EntryCreated::class,
        'updated' => EntryUpdated::class,
        'trashed' => EntryTrashed::class,
        'restored' => EntryRestored::class,
        'forceDeleted' => EntryForceDeleted::class,
    ];

//    public function newUniqueId(): UserIdAttribute
//    {
//        return new UserIdAttribute();
//    }

    public function uniqueIds(): array
    {
        return ['entry_id'];
    }

    public function entryGroup(): BelongsTo
    {
        return $this->belongsTo(EntryGroup::class, 'entry_group_id', 'entry_group_id');
    }

    public function passwords(): HasMany
    {
        return $this->hasMany(Password::class, 'entry_id', 'entry_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'entry_id', 'entry_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'entry_id', 'entry_id');
    }

    public function TOTPs(): HasMany
    {
        return $this->hasMany(TOTP::class, 'entry_id', 'entry_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'entry_id', 'entry_id');
    }

    public function fields(): Collection
    {
        $fields = new Collection();
        $fields = $fields->merge($this->passwords()->get());
        $fields = $fields->merge($this->links()->get());
        $fields = $fields->merge($this->files()->get());
        $fields = $fields->merge($this->notes()->get());
        return $fields->merge($this->TOTPs()->get());
    }

    public function addPassword(
        UserIdAttribute $userId,
        string          $password_encrypted,
        string          $initializing_vector,
        string          $login,
        string          $title = ""
    ): Password
    {
        $password = new Password([
            'entry_id' => $this->entry_id,
            'title' => Title::fromNative($title),
            'login' => Login::fromNative($login),
            'value_encrypted' => ValueEncrypted::fromNative($password_encrypted),
            'initialization_vector' => InitializationVector::fromNative($initializing_vector),
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $password->field_id;
        $password->save();
        return $password;
    }

    public function addLink(
        UserIdAttribute $userId,
        string          $link_encrypted,
        string          $initializing_vector,
        string          $title = ""
    ): Link
    {
        $link = new Link([
            'entry_id' => $this->entry_id,
            'title' => Title::fromNative($title),
            'value_encrypted' => ValueEncrypted::fromNative($link_encrypted),
            'initialization_vector' => InitializationVector::fromNative($initializing_vector),
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $link->field_id;
        $link->save();
        return $link;
    }

    public function addFile(
        UserIdAttribute $userId,
        string          $file_encrypted,
        string          $initializing_vector,
        string          $title = "",
        ?int            $file_size = null,
        ?string         $file_name = null,
        ?string         $file_mime = null
    ): File
    {
        $file = new File([
            'entry_id' => $this->entry_id,
            'title' => Title::fromNative($title),
            'file_name' => FileName::fromNative($file_name),
            'file_size' => FileSize::fromNative($file_size),
            'file_mime' => FileMime::fromNative($file_mime),
            'value_encrypted' => ValueEncrypted::fromNative($file_encrypted),
            'initialization_vector' => InitializationVector::fromNative($initializing_vector),
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $file->field_id;
        $file->save();
        return $file;
    }

    public function addNote(
        UserIdAttribute $userId,
        string          $note_encrypted,
        string          $initializing_vector,
        string          $title = ""
    ): Note
    {
        $note = new Note([
            'entry_id' => $this->entry_id,
            'title' => Title::fromNative($title),
            'value_encrypted' => ValueEncrypted::fromNative($note_encrypted),
            'initialization_vector' => InitializationVector::fromNative($initializing_vector),
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $note->field_id;
        $note->save();
        return $note;
    }

    public function addTOTP(
        UserIdAttribute   $userId,
        string            $TOPT_encrypted,
        string            $initializing_vector,
        TOTPHashAlgorithm $totp_hash_algorithm,
        int               $totp_timeout,
        string            $title = "",
    ): TOTP
    {
        $TOTP = new TOTP([
            'entry_id' => $this->entry_id,
            'title' => Title::fromNative($title),
            'value_encrypted' => ValueEncrypted::fromNative($TOPT_encrypted),
            'initialization_vector' => InitializationVector::fromNative($initializing_vector),
            'totp_hash_algorithm' => $totp_hash_algorithm,
            'totp_timeout' => TOTPTimeout::fromNative($totp_timeout),
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $TOTP->field_id;
        $TOTP->save();

        return $TOTP;
    }

    public function validate(EntryValidationHandler $validationHandler): void
    {
        (new EntryValidator($this, $validationHandler))->validate();
    }
}
