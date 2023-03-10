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
        $table = app(User::class)->getTable();

        /**
         * @var User $userTarget
         */
        $userTarget = request()?->route('user');

        return [
            'user.email' => [
                'required',
                'email',
                Rule::unique($table, 'email')->ignore($userTarget)
            ],

            'user.username' => 'required|min:1|unique:' . $table . ',name,' . $userTarget->user_id->getValue(),
            'user.password' => [
                'nullable',
                'confirmed',
                Password::min(5)->letters()->numbers()
            ],
        ];
    }

}
