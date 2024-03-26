<?php

namespace PasswordBroker\Domain\Entry\Models\Groups;

use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\RoleMemberCreated;
use PasswordBroker\Application\Events\RoleMemberDeleted;

#[Schema(
    schema: "PasswordBroker_Role_Member",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Role"),
        new AdditionalProperties(properties: [
            new Property(property: "role", enum: ["member"]),
        ]),
    ],
)]
class Member extends Role
{
    public const ROLE_NAME = 'member';

    protected $attributes = ['role' => self::ROLE_NAME];

    protected $dispatchesEvents = [
//        'saving' => FieldSave::class,
        'created' => RoleMemberCreated::class,
        'deleted' => RoleMemberDeleted::class,
//        'updated' => FieldUpdated::class,
//        'trashed' => FieldTrashed::class,
//        'restored' => FieldRestored::class,
//        'forceDeleted' => FieldForceDeleted::class,
    ];
}
