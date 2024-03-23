<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_RegisterUserRequest",
    properties: [
        new Property(
            property: "user",
            properties: [
                new Property(property: "email", type: "string", format: "email", nullable: false),
                new Property(property: "name", type: "string", nullable: false),
                new Property(property: "password", type: "string", format: "password", nullable: false),
                new Property(property: "password_confirmation", type: "string", format: "password", nullable: false),
                new Property(property: "master_password", type: "string", format: "password", nullable: false),
                new Property(property: "master_password_confirmation", type: "string", format: "password", nullable: false),
            ],
        ),
    ],
    type: "object",
)]
class RegisterUserRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        $table = app(User::class)->getTableFullName();
        return [
            'user.email' => 'required|email|unique:' . $table . ',email',
            'user.username' => 'required|min:1|unique:' . $table . ',name',
            'user.password' => [
                'required',
                'confirmed', //password_confirmation
                Password::min(5)->letters()->numbers()
            ],
            'user.master_password' => [
                'required',
                'confirmed',
                Password::min(6)->letters()->numbers()
            ]
        ];
    }

}
