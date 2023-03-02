<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        $table = app(User::class)->getTable();

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
