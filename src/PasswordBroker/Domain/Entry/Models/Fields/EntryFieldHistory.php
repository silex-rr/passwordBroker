<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Attributes\UserId as UserIdAttribute;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Login;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldEditLog\EventType;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldEditLogId;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\FieldId;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\InitializationVector;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\IsDeleted;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\Title;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\UpdatedBy;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\ValueEncrypted;

/**
 * @property Attributes\FieldEditLogId field_edit_log_id
 * @property Attributes\FieldId $field_id
 * @property Attributes\Title $title
 * @property string $type - Class of Field type to what the log belong to
 * @property Attributes\FieldEditLog\EventType $event_type
 * @property Attributes\ValueEncrypted $value_encrypted
 * @property Attributes\InitializationVector $initialization_vector
 * @property Attributes\IsDeleted $is_deleted
 * @property UserIdAttribute $updated_by
 * @property Login|null $login
 * @method static Builder belongToEntryGroup(EntryGroup $entryGroup)
 * @method Builder belongToEntryGroup(EntryGroup $entryGroup)
 */
class EntryFieldHistory extends Model
{
    use ModelDomainConstructor;
    use HasUuids;

    public $table = 'entry_field_history';
    public $incrementing = false;
    public $keyType = 'string';
    protected $primaryKey = 'field_edit_log_id';

    public $guarded = [
        'type'
    ];
    public $fillable = [
        'field_edit_log_id',
        'field_id',
        'title',
        'login',
        'event_type',
        'value_encrypted',
        'initialization_vector',
        'is_deleted',
        'updated_by'
    ];

    public $casts = [
        'field_edit_log_id' => FieldEditLogId::class,
        'field_id' => FieldId::class,
        'title' => Title::class,
        'login' => Casts\Login::class,
        'event_type' => EventType::class,
        'value_encrypted' => ValueEncrypted::class,
        'initialization_vector' => InitializationVector::class,
        'is_deleted' => IsDeleted::class,
        'updated_by' => UpdatedBy::class,
    ];
    protected $hidden = [
        'value_encrypted',
        'initialization_vector',
        'type'
    ];

    public function field(): BelongsTo
    {
        return $this->morphTo(__FUNCTION__, 'type', 'field_id', 'field_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function scopeBelongToEntryGroup(Builder $q, EntryGroup $entryGroup): void
    {
        $q->whereHasMorph('field', Field::getRelated(), static function (Builder $q) use($entryGroup) {
            $q->whereHas('entry', function ($q) use($entryGroup) {
                $q->where('entry_group_id', $entryGroup->entry_group_id->getValue());
            });
        });
    }
}
