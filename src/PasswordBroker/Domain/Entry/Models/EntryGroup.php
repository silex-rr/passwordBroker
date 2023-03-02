<?php

namespace PasswordBroker\Domain\Entry\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use App\Models\Abstracts\AbstractValue;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserToGroupTranslator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PasswordBroker\Domain\Entry\Models\Casts\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Casts\GroupName;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Attributes\EncryptedAesPassword;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Infrastructure\Validation\EntryGroupUserValidator;
use PasswordBroker\Infrastructure\Validation\EntryGroupValidator;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupUserValidationHandler;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;

/**
 * @property Attributes\EntryGroupId $entry_group_id
 */
class EntryGroup extends Model
{
//    use CastsValuesToObjects;
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    protected $primaryKey = 'entry_group_id';
    public $incrementing = false;
    public $keyType = 'string';
    public $fillable = ['name'];


    public $casts = [
        'entry_group_id' => EntryGroupId::class,
        'name' => GroupName::class
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
}
