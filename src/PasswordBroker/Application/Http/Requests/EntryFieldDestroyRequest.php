<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use phpDocumentor\Reflection\Type;

/**
 * @property string master_password
 */
#[Schema(
    schema: "PasswordBroker_EntryFieldDestroyRequest",
    properties: [
        new Property(property: "master_password", type: "string"),
    ],
)]
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
