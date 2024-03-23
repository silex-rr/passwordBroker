<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_LoginRequest",
    properties: [
        new Property(property: "email", type: "string", format: "email", nullable: false,),
        new Property(property: "password", type: "string", format: "password", nullable: false,),
        new Property(property: "token_is_required", type: "boolean", nullable: true),
        new Property(property: "token_name", type: "string", nullable: true),
    ],
    type: "object",
)]
class LoginRequest extends FormRequest
{
    public function rules() : array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'token_is_required' => 'boolean',
            'token_name' => 'string|required_with:token_is_required'
        ];
    }

    public function getTokenName(): ?string
    {
        return $this->get('token_name');
    }

    public function isTokenRequired(): bool
    {
        return !is_null($this->get('token_is_required'));
    }
}
