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
    schema: "Identity_RecoveryUserLandingRequest",
    properties: [
        new Property(
            property: "user",
            properties: [
                new Property(property: "password", type: "string", format: "password", nullable: false),
                new Property(property: "password_confirmation", type: "string", format: "password", nullable: false),
            ],
        ),
        new Property(
            property: "fingerprint", type: "string", format: "json", nullable: true
        )
    ],
    type: "object",
)]
class RecoveryUserLandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user.password' => [
                'required',
                'confirmed', //password_confirmation
                Password::min(5)->letters()->numbers()
            ],
            'fingerprint' => 'nullable|json',
        ];
    }

}
