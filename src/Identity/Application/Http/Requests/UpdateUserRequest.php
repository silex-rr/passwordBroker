<?php

namespace Identity\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{

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
