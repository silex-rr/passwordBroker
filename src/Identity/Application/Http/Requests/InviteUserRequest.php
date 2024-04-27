<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

/**
 * @property
 */
#[Schema(
    schema: "Identity_InviteUserRequest",
    properties: [
        new Property(
            property: "user",
            properties: [
                new Property(property: "email", type: "string", format: "email", nullable: false),
                new Property(property: "username", type: "string", nullable: true),
            ],
        ),
        new Property(
            property: "fingerprint", type: "string", format: "json", nullable: true
        )
    ],
    type: "object",
)]
class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $table = app(User::class)->getTableFullName();

        return [
            'user.email' => 'required|email|unique:' . $table . ',email',
            'user.username' => 'nullable|min:1|unique:' . $table . ',name',
            'fingerprint' => 'nullable|json',
        ];
    }

}
