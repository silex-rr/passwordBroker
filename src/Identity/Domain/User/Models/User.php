<?php

namespace Identity\Domain\User\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Events\UserWasCreated;
use Identity\Domain\User\Events\UserWasUpdated;
use Identity\Domain\User\Models\Casts\Email;
use Identity\Domain\User\Models\Casts\IsAdmin;
use Identity\Domain\User\Models\Casts\PublicKey;
use Identity\Domain\User\Models\Casts\UserId;
use Identity\Domain\User\Models\Casts\UserName;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

/**
 * @property Attributes\UserId $user_id
 * @property Attributes\PublicKey $public_key
 * @property Attributes\IsAdmin $is_admin
 * @property string $password
 * @property Attributes\UserName $name
 * @property Attributes\Email $email
 */
class User extends Authenticatable
{
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    use HasApiTokens;
    use Notifiable;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = ['email', 'name', 'is_admin', 'public_key'];
    protected $casts = [
        'user_id' => UserId::class,
        'email' => Email::class,
        'name' => UserName::class,
        'is_admin' => IsAdmin::class,
        'public_key' => PublicKey::class,
        'email_verified_at' => 'datetime',
    ];
    protected $dispatchesEvents = [
        'created' => UserWasCreated::class,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getId_UserAttribute(): Attributes\UserId
    {
        return $this->user_id;
    }

    public function applications(): HasMany
    {
        return $this->hasMany(UserApplication::class, 'user_id', 'user_id');
    }

    public function adminOf(): HasMany
    {
        return $this->hasMany(Admin::class, 'user_id', 'user_id')
            ->where('role', Admin::ROLE_NAME)
            ->withWhereHas('entryGroup');
    }

    public function moderatorOf(): HasMany
    {
        return $this->hasMany(Moderator::class, 'user_id', 'user_id')
            ->where('role', Moderator::ROLE_NAME)
            ->withWhereHas('entryGroup');
    }

    public function memberOf(): HasMany
    {
        return$this->hasMany(Member::class, 'user_id', 'user_id')
            ->where('role', Member::ROLE_NAME)
            ->withWhereHas('entryGroup');
    }

    /**
     * @return Collection
     */
    public function userOf(): Collection
    {
        $groups = new Collection();
        $groups = $groups->merge($this->adminOf()->get());
        $groups = $groups->merge($this->moderatorOf()->get());
        return    $groups->merge($this->memberOf()->get());
    }

    public function addAsAdminOf(EntryGroup $entryGroup, string $encrypted_aes_password): void
    {
        $this->adminOf[] = $entryGroup;
        $entryGroup->addAdmin($this, $encrypted_aes_password);
    }

    public function addAsModeratorOf(EntryGroup $entryGroup, string $encrypted_aes_password): void
    {
        $this->moderatorOf[] = $entryGroup;
        $entryGroup->addModerator($this, $encrypted_aes_password);
    }

    public function addAsMemberOf(EntryGroup $entryGroup, string $encrypted_aes_password): void
    {
        $this->memberOf[] = $entryGroup;
        $entryGroup->addMember($this, $encrypted_aes_password);
    }
}
