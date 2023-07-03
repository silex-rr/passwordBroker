<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string master_password
 */
class EntryFieldDestroyRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'master_password' => 'required|string|min:1',
        ];
    }

    public function getMasterPassword(): string
    {
        return $this->master_password;
    }

}
