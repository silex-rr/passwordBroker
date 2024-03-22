<?php

namespace PasswordBroker\Domain\Entry\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use App\Models\Abstracts\AbstractValue;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\EntryGroupCreated;
use PasswordBroker\Application\Events\EntryGroupForceDeleted;
use PasswordBroker\Application\Events\EntryGroupRestored;
use PasswordBroker\Application\Events\EntryGroupTrashed;
use PasswordBroker\Application\Events\EntryGroupUpdated;
use PasswordBroker\Domain\Entry\Models\Casts\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Casts\GroupName;
use PasswordBroker\Domain\Entry\Models\Casts\MaterializedPath;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Attributes\EncryptedAesPassword;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Infrastructure\Factories\Entry\EntryGroupFactory;
use PasswordBroker\Infrastructure\Validation\EntryGroupUserValidator;
use PasswordBroker\Infrastructure\Validation\EntryGroupValidator;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupUserValidationHandler;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;
use Symfony\Component\Mime\Encoder\Base64Encoder;

/**
 * @property Attributes\EntryGroupId $entry_group_id
 * @property Attributes\GroupName $name
 * @property Attributes\MaterializedPath $materialized_path
 *
 * @method static EntryGroupFactory factory
 */

#[Schema(
    schema: "PasswordBroker_EntryGroup",
    properties: [
        new Property(property: "entry_group_id", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
        new Property(property: "name", ref: "#/components/schemas/PasswordBroker_GroupName"),
        new Property(property: "materialized_path", ref: "#/components/schemas/PasswordBroker_MaterializedPath"),
        new Property(property: "created_at", type: "date-time"),
        new Property(property: "updated_at", type: "date-time", nullable: true),
        new Property(property: "deleted_at", type: "date-time", nullable: true),
    ],
    type: "object",
)]
class EntryGroup extends Model
{
//    use CastsValuesToObjects;
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    use SoftDeletes;
    protected $primaryKey = 'entry_group_id';
    public $incrementing = false;
    public $keyType = 'string';
    public $fillable = ['name'];


    public $casts = [
        'entry_group_id' => EntryGroupId::class,
        'name' => GroupName::class,
        'materialized_path' => MaterializedPath::class
    ];

    protected $dispatchesEvents = [
        'created' => EntryGroupCreated::class,
        'updated' => EntryGroupUpdated::class,
        'trashed' => EntryGroupTrashed::class,
        'restored' => EntryGroupRestored::class,
        'forceDeleted' => EntryGroupForceDeleted::class,
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'entry_group_id', 'entry_group_id');
    }

    public function parentEntryGroup(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_entry_group_id', 'entry_group_id');
    }

    public function entryGroups(): HasMany
    {
        return $this->hasMany(static::class, 'parent_entry_group_id', 'entry_group_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'entry_group_id', 'entry_group_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'entry_group_id', 'entry_group_id');
    }

    public function moderators(): HasMany
    {
        return $this->hasMany(Moderator::class, 'entry_group_id', 'entry_group_id');
    }

    public function users(): Collection
    {
        $users = new Collection();
        $users = $users->merge($this->admins()->with('user')->get());
        $users = $users->merge($this->moderators()->with('user')->get());
        return   $users->merge($this->members()->with('user')->get());
    }

    public function addAdmin(User $user, string $encrypted_aes_password): void
    {
        Admin::firstOrCreate([
            'user_id' => $user->user_id,
            'entry_group_id' => $this->entry_group_id,
            'encrypted_aes_password' => new EncryptedAesPassword($encrypted_aes_password)
        ]);
    }

    public function addModerator(User $user, string $encrypted_aes_password): void
    {
        Moderator::firstOrCreate([
            'user_id' => $user->user_id,
            'entry_group_id' => $this->entry_group_id,
            'encrypted_aes_password' => new EncryptedAesPassword($encrypted_aes_password)
        ]);
    }

    public function addMember(User $user, string $encrypted_aes_password): void
    {
        Member::firstOrCreate([
            'user_id' => $user->user_id,
            'entry_group_id' => $this->entry_group_id,
            'encrypted_aes_password' => new EncryptedAesPassword($encrypted_aes_password)
        ]);
    }

//    public function getAdminsAttribute(): Collection
//    {
//        return $this->getRoleAttribute($this->admins, 'toAdmin');
//    }
//
//    private function getRoleAttribute(Collection $users, string $convertor): Collection
//    {
//        $userToGroupTranslator = new UserToGroupTranslator();
//        return $users->map(fn(User $user) => $userToGroupTranslator->{$convertor}($user));
//    }
//
//    public function getModeratorsAttribute(): Collection
//    {
//        return $this->getRoleAttribute($this->moderators, 'toModerator');
//    }
//
//    public function getMemberAttribute(): Collection
//    {
//        return $this->getRoleAttribute($this->members, 'toMember');
//    }

    public function getCasts(): array
    {
        return $this->casts;
    }

    public function is($model): bool
    {
        return is_object($model)
            && get_class($model) === static::class
            && $this->entry_group_id instanceof AbstractValue
            && $model->entry_group_id instanceof AbstractValue
            && $this->entry_group_id->equals($model->entry_group_id);
    }

    public function validate(EntryGroupValidationHandler $validationHandler): void
    {
        (new EntryGroupValidator($this, $validationHandler))->validate();
    }

    public function validateUser(EntryGroupUserValidationHandler $validationHandler, User $user): void
    {
        (new EntryGroupUserValidator($this, $user, $validationHandler))->validate();
    }

    public function encryptedEntryGroupIdBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->entry_group_id->getValue())
        );
    }

    public function fieldHistories(): HasMany
    {
        return $this->entries()->with(
            array_map( static fn($a) => $a . '.fieldHistories',
                array_map('\Illuminate\Support\Str::plural', array_keys((Field::getRelated())))
            )
        );
    }
}
