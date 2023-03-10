<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Attributes\UserId as UserIdAttribute;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PasswordBroker\Application\Events\FieldSave;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryId as EntryIdAttribute;
use PasswordBroker\Domain\Entry\Models\Casts\EntryId;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\CreatedBy;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldId;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\InitializationVector;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\Title;
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
abstract class Field extends Model
{
    public const TYPE = '';
    protected static array $related = [
        Password::TYPE => Password::class,
        Link::TYPE => Link::class,
        Note::TYPE => Note::class
    ];

    use HasUuids;
    public $table = 'entry_fields';
    public $incrementing = false;
    public $keyType = 'string';

    public $fillable = [
        'field_id',
        'entry_id',
        'title',
        'value_encrypted',
        'initialization_vector',
        'created_by',
        'update_by'
    ];
    public $guarded = [
        'type'
    ];
    public $casts = array(
        'field_id' => FieldId::class,
        'entry_id' => EntryId::class,
        'title' => Title::class,
        'value_encrypted' => ValueEncrypted::class,
        'initialization_vector' => InitializationVector::class,
        'created_by' => CreatedBy::class,
        'updated_by' => UpdatedBy::class,
    );

    protected $hidden = [
        'value_encrypted',
        'initialization_vector'
    ];

    protected $appends = [
        'encrypted_value_base64',
        'initialization_vector_base64'
    ];

    protected $primaryKey = 'field_id';
    protected $dispatchesEvents = [
        'saving' => FieldSave::class
    ];

    use ModelDomainConstructor;

    public static function create(
        UserIdAttribute                 $userId,
        EntryIdAttribute                $entryId,
        Attributes\Title                $title,
        Attributes\ValueEncrypted       $value_encrypted,
        Attributes\InitializationVector $initialization_vector
    ): self
    {
        $field = new static([
            'entry_id' => $entryId,
            'title' => $title,
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

}
