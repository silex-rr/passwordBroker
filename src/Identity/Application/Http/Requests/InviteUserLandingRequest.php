<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

/**
 * @property
 */
#[Schema(
    schema: "Identity_InviteUserLandingRequest",
    properties: [
        new Property(
            property: "user",
            properties: [
                new Property(property: "username", type: "string", nullable: true),
                new Property(property: "password", type: "string", format: "password", nullable: false),
                new Property(property: "password_confirmation", type: "string", format: "password", nullable: false),
                new Property(property: "master_password", type: "string", format: "password", nullable: false),
                new Property(property: "master_password_confirmation", type: "string", format: "password", nullable: false),
            ],
        ),
        new Property(
            property: "fingerprint", type: "string", format: "json", nullable: true
        )
    ],
    type: "object",
)]
class InviteUserLandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $table = app(User::class)->getTableFullName();

        return [
            'user.username' => 'nullable|min:1|unique:' . $table . ',name',
            'user.password' => [
                'required',
                'confirmed', //password_confirmation
                Password::min(5)->letters()->numbers()
            ],
            'user.master_password' => [
                'required',
                'confirmed',
                Password::min(6)->letters()->numbers()
            ],
            'fingerprint' => 'nullable|json',
        ];
    }

}
