<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_UpdateUserRequest",
    properties: [
        new Property(property:"email", ref: "#/components/schemas/Identity_UpdateUserRequest_email"),
        new Property(property:"username", ref: "#/components/schemas/Identity_UpdateUserRequest_username"),
        new Property(property:"password", ref: "#/components/schemas/Identity_UpdateUserRequest_password"),
    ],
    type: "object",
)]
class UpdateUserRequest extends FormRequest
{
    #[Schema(schema: "Identity_UpdateUserRequest_email", type: "string", format: "email",)]
    public string $email;
    #[Schema(schema: "Identity_UpdateUserRequest_username", type: "string", nullable: true,)]
    public ?string $username = '';
    #[Schema(schema: "Identity_UpdateUserRequest_password", type: "string", format: "password", nullable: true,)]
    public ?string $password = '';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**
         * @var User $userTarget
         */
        $userTarget = request()?->route('user');

        return [
            'email' => [
                'required',
                'email',
                Rule::unique($userTarget->getTableFullName(), 'email')->ignore($userTarget)
            ],

            'username' => [
                'required',
                'min:1',
                Rule::unique($userTarget->getTableFullName(), 'name')->ignore($userTarget)
            ],

            'password' => [
                'nullable',
                'confirmed',
                Password::min(5)->letters()->numbers()
            ],
        ];
    }

}
