<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use App\Common\Domain\Traits\AppendGlobal;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Casts\UserId;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Contracts\RoleInterface;
use PasswordBroker\Domain\Entry\Models\Casts\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Casts\EncryptedAesPassword;
use Symfony\Component\Mime\Encoder\Base64Encoder;

/**
 * @property \Identity\Domain\User\Models\Attributes\UserId $user_id
 * @property \PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId $entry_group_id
 * @property Attributes\EncryptedAesPassword $encrypted_aes_password
 */
#[Schema(
    schema: "PasswordBroker_Role",
    properties: [
        new Property(property: "user_id", ref: "#/components/schemas/Identity_UserId"),
        new Property(property: "entry_group_id", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
    ],
)]
abstract class Role extends Model
    implements RoleInterface
{

    use AppendGlobal;
    use ModelDomainConstructor;
    public const ROLE_NAME = '';

    public $table = 'entry_group_user';

    public $fillable = [
        'user_id',
        'entry_group_id',
        'role',
        'encrypted_aes_password'
    ];

    public $guarded = [
        'role'
    ];

    protected $hidden = [
        'encrypted_aes_password',
    ];

    public $casts = [
        'user_id' => UserId::class,
        'entry_group_id' => EntryGroupId::class,
        'encrypted_aes_password' => EncryptedAesPassword::class
    ];

    public static function appendEncryptedAesPasswordBase64(): void
    {
        static::appendField('encrypted_aes_password_base64');
    }

    public static function create(User $user, EntryGroup $entryGroup): self
    {
        $role = new static(['user_id' => $user->user_id, 'entry_group_id' => $entryGroup->entry_group_id]);
        $role->save();
        return $role;
    }

    public function entryGroup(): BelongsTo
    {
        return $this->belongsTo(EntryGroup::class, 'entry_group_id', 'entry_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function newQuery($excludeDeleted = true): Builder
    {
        return parent::newQuery($excludeDeleted)
            ->where('role', '=', $this->getRoleName());
    }

    public function getRoleName(): string
    {
        return static::ROLE_NAME;
    }

    public function encryptedEntryGroupIdBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->entry_group_id->getValue())
        );
    }

    public function encryptedUserIdBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->user_id->getValue())
        );
    }

    protected function encryptedAesPasswordBase64(): Attribute
    {
        return new Attribute(
            get: fn () => app(Base64Encoder::class)->encodeString($this->encrypted_aes_password->getValue())
        );
    }
}
