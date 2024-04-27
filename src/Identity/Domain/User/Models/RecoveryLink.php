<?php

namespace Identity\Domain\User\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Carbon\Carbon;
use Identity\Domain\User\Models\Casts\Fingerprint;
use Identity\Domain\User\Models\Casts\RecoveryLinkId;
use Identity\Domain\User\Models\Casts\RecoveryLinkKey;
use Identity\Domain\User\Models\Casts\RecoveryLinkStatus;
use Identity\Domain\User\Models\Casts\RecoveryLinkType;
use Identity\Domain\User\Models\Casts\UserId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Queue\SerializesModels;

/**
 * Class RecoveryLink
 *
 * @property Attributes\UserId $recovery_link_id
 * @property Attributes\UserId $user_id
 * @property Attributes\UserId $issued_by_user_id
 * @property Attributes\RecoveryLinkKey $key
 * @property Attributes\RecoveryLinkType $type
 * @property Attributes\RecoveryLinkStatus $status
 * @property Carbon $expired_at,
 * @property Carbon $activated_at,
 * @property Attributes\Fingerprint $created_by_fingerprint
 * @property Attributes\Fingerprint $activated_by_fingerprint
 *
 */
class RecoveryLink extends Model
{
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    use SerializesModels;

    public $serializer = 'asd';
    protected $primaryKey = 'recovery_link_id';
    public $incrementing = false;
    public $keyType = 'string';

    protected $casts = [
        'recovery_link_id' => RecoveryLinkId::class,
        'user_id' => UserId::class,
        'issued_by_user_id' => UserId::class,
        'key' => RecoveryLinkKey::class,
        'type' => RecoveryLinkType::class,
        'status' => RecoveryLinkStatus::class,
        'expired_at' => 'datetime',
        'activated_at' => 'datetime',
        'created_by_fingerprint' => Fingerprint::class,
        'activated_by_fingerprint' => Fingerprint::class,
    ];

    public function uniqueIds(): array
    {
        return ['recovery_link_id'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function issuedByUser(): BelongsTo|null
    {
        return $this->belongsTo(User::class, 'user_id', 'issued_by_user_id');
    }
}
