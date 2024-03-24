<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

/**
 * @property string master_password
 */
#[Schema(schema: "PasswordBroker_EntryFieldDecryptedRequest", type: "string")]
class EntryFieldDecryptedRequest extends FormRequest
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
