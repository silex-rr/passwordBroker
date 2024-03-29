<?php

namespace Identity\Domain\UserApplication\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Identity\Domain\User\Models\Casts\UserId;
use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Casts\ClientId;
use Identity\Domain\UserApplication\Models\Casts\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\Casts\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Casts\IsRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\Casts\UserApplicationId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

/**
 * @property Carbon offline_database_fetched_at
 * @property Carbon rsa_private_fetched_at
 * @property Attributes\IsOfflineDatabaseMode is_offline_database_mode
 * @property Attributes\UserApplicationId user_application_id
 * @property Attributes\ClientId client_id
 * @property Attributes\IsOfflineDatabaseRequiredUpdate is_offline_database_required_update
 * @property Attributes\IsRsaPrivateRequiredUpdate is_rsa_private_required_update
 */
#[Schema(
    schema: "Identity_UserApplication",
    properties: [
        new Property(property: "userApplicationId", ref: "#/components/schemas/Identity_UserApplicationId"),
        new Property(property: "clientId", ref: "#/components/schemas/Identity_ClientId"),
        new Property(property: "isOfflineDatabaseRequiredUpdate", ref: "#/components/schemas/Identity_IsOfflineDatabaseRequiredUpdate"),
        new Property(property: "isRsaPrivateRequiredUpdate", ref: "#/components/schemas/Identity_IsRsaPrivateRequiredUpdate"),
        new Property(property: "isOfflineDatabaseMode", ref: "#/components/schemas/Identity_IsOfflineDatabaseMode"),
        new Property(property: "offlineDatabaseFetchedAt", type: "string", format: "date-time"),
        new Property(property: "rsaPrivateFetchedAt", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class UserApplication extends Model
{
    use ModelDomainConstructor;
    use HasFactoryDomain;
    use HasUuids;
    use SoftDeletes;

    protected $primaryKey = 'user_application_id';
    public $incrementing = false;
    public $keyType = 'string';

    protected $guarded = ['user_application_id'];
    protected $casts = [
        'user_application_id' => UserApplicationId::class,
        'user_id' => UserId::class,
        'client_id' => ClientId::class,
        'is_offline_database_mode' => IsOfflineDatabaseMode::class,
        'is_offline_database_required_update' => IsOfflineDatabaseRequiredUpdate::class,
        'is_rsa_private_required_update' => IsRsaPrivateRequiredUpdate::class,
        'offline_database_fetched_at' => 'datetime',
        'rsa_private_fetched_at' => 'datetime',
    ];
    public function uniqueIds(): array
    {
        return ['user_application_id'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
