<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
