<?php

namespace PasswordBroker\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

class EntryGroupUserRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'target_user_id' => 'required|exists:' . User::class . ',user_id',
            'role' => Rule::in([Admin::ROLE_NAME, Moderator::ROLE_NAME, Member::ROLE_NAME]),
            'encrypted_aes_password' => 'required_without:master_password|string|min:1',
            'master_password' => 'required_without:encrypted_aes_password|string|min:1'
        ];
    }

    public function targetUser(): User
    {
        return User::where('user_id', $this->get('target_user_id'))->firstOrFail();
    }

}
