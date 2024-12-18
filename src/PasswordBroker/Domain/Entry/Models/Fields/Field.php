<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use App\Common\Domain\Traits\AppendGlobal;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Attributes\UserId as UserIdAttribute;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\FieldCreated;
use PasswordBroker\Application\Events\FieldForceDeleted;
use PasswordBroker\Application\Events\FieldRestored;
use PasswordBroker\Application\Events\FieldTrashed;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryId as EntryIdAttribute;
use PasswordBroker\Domain\Entry\Models\Casts\EntryId;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\CreatedBy;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldId;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FileMime;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FileName;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FileSize;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\InitializationVector;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\Title;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\TOTPTimeout;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\UpdatedBy;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\ValueEncrypted;
use Symfony\Component\Mime\Encoder\Base64Encoder;

/**
 * @property Attributes\FieldId $field_id
 * @property EntryIdAttribute $entry_id
 * @property Attributes\Title $title
 * @property Attributes\ValueEncrypted $value_encrypted
 * @property Attributes\InitializationVector $initialization_vector
 * @property UserIdAttribute $created_by
 * @property UserIdAttribute $updated_by
 */
#[Schema(
    schema: "PasswordBroker_Field",
    properties: [
        new Property(property: "field_id", ref: "#/components/schemas/PasswordBroker_FieldId"),
        new Property(property: "entry_id", ref: "#/components/schemas/PasswordBroker_EntryId"),
        new Property(property: "title", ref: "#/components/schemas/PasswordBroker_FieldTitle"),
        new Property(property: "type", type: "string", enum: [Password::TYPE, Link::TYPE, Note::TYPE, File::TYPE, TOTP::TYPE],),
        new Property(property: "value_encrypted", ref: "#/components/schemas/PasswordBroker_ValueEncrypted"),
        new Property(property: "initialization_vector", ref: "#/components/schemas/PasswordBroker_InitializationVector"),
        new Property(property: "created_by", ref: "#/components/schemas/Identity_UserId"),
        new Property(property: "updated_by", ref: "#/components/schemas/Identity_UserId"),
        new Property(property: "created_at", type: "string", format: "date-time"),
        new Property(property: "updated_at", type: "string", format: "date-time", nullable: true,),
        new Property(property: "deleted_at", type: "string", format: "date-time", nullable: true,),
    ],
)]
abstract class Field extends Model
{
    use HasUuids;
    use ModelDomainConstructor;
    use SoftDeletes;
    use AppendGlobal;

    public const TYPE = '';
    protected static array $related = [
        Password::TYPE => Password::class,
        Link::TYPE => Link::class,
        Note::TYPE => Note::class,
        File::TYPE => File::class,
        TOTP::TYPE => TOTP::class,
    ];

    public $table = 'entry_fields';
    public $incrementing = false;
    public $keyType = 'string';

    public $fillable = [
        'field_id',
        'entry_id',
        'title',
        'file_name',
        'file_size',
        'file_mime',
        'login',
        'totp_hash_algorithm',
        'totp_timeout',
        'value_encrypted',
        'initialization_vector',
        'created_by',
        'updated_by'
    ];
    public $guarded = [
        'type'
    ];

    public $casts = [
        'field_id' => FieldId::class,
        'entry_id' => EntryId::class,
        'title' => Title::class,
        'file_name' => FileName::class,
        'file_mime' => FileMime::class,
        'file_size' => FileSize::class,
        'login' => Login::class,
        'totp_hash_algorithm' => TOTPHashAlgorithm::class,
        'totp_timeout' => TOTPTimeout::class,
        'value_encrypted' => ValueEncrypted::class,
        'initialization_vector' => InitializationVector::class,
        'created_by' => CreatedBy::class,
        'updated_by' => UpdatedBy::class,
    ];

    protected $hidden = [
        'value_encrypted',
        'initialization_vector',
        'file_name',
        'file_size',
        'file_mime',
        'login',
        'totp_timeout',
        'totp_hash_algorithm',
    ];

    protected $appends = [
//        'encrypted_value_base64',
//        'initialization_vector_base64'
    ];
    protected $primaryKey = 'field_id';
    protected $dispatchesEvents = [
//        'saving' => FieldSave::class,
        'created' => FieldCreated::class,
        'updated' => FieldUpdated::class,
        'trashed' => FieldTrashed::class,
        'restored' => FieldRestored::class,
        'forceDeleted' => FieldForceDeleted::class,
    ];


    public static function appendEncryptedValueBase64(): void
    {
        static::appendField('encrypted_value_base64');
    }
    public static function appendInitializationVectorBase64(): void
    {
        static::appendField('initialization_vector_base64');
    }

    public static function create(
        UserIdAttribute                 $userId,
        EntryIdAttribute                $entryId,
        Attributes\Title                $title,
        Attributes\ValueEncrypted       $value_encrypted,
        Attributes\InitializationVector $initialization_vector,
        ?Attributes\FileName            $file_name = null,
        ?Attributes\FileSize            $file_size = null,
        Attributes\Login                $login = null,
        ?Attributes\TOTPHashAlgorithm   $totp_hash_algorithm = null,
        ?Attributes\TOTPTimeout         $totp_timeout = null,
    ): self
    {
        $field = new static([
            'entry_id' => $entryId,
            'title' => $title,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'login' => $login,
            'totp_hash_algorithm' => $totp_hash_algorithm,
            'totp_timeout' => $totp_timeout,
            'value_encrypted' => $value_encrypted,
            'initialization_vector' => $initialization_vector,
            'created_by' => $userId,
            'updated_by' => $userId
        ]);
        $field->save();
        return $field;
    }

    public static function getFiledByFieldId(string $field_id): self
    {
        /**
         * @var Field $field
         */
        $field = app(Password::class);
        /**
         * @var object $field_data
         */
        $field_data = $field->getConnection()->table($field->getTable())->where('field_id', $field_id)->first();

        return self::$related[$field_data->type]::hydrate([(array)$field_data])->first();
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'entry_id', 'entry_id');
    }

    public function fieldHistories(): MorphMany
    {
        return $this->morphMany(EntryFieldHistory::class, 'field', 'type', 'field_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function newQuery($excludeDeleted = true): Builder
    {
        return parent::newQuery($excludeDeleted)->where('type', '=', $this->getType());
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public static function getRelated(): array
    {
        return self::$related;
    }

    public static function getRelatedForWith(): array
    {
        return array_map(static fn (string $a) => lcfirst($a) . 's', array_keys(static::getRelated()));
    }

    protected function encryptedValueBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->value_encrypted->getValue())
        );
    }
    protected function initializationVectorBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->initialization_vector->getValue())
        );
    }

//    public function scopeBelongToGroup(Builder $query, EntryGroup $entryGroup): void
//    {
//        $query->where('')
//    }
}
