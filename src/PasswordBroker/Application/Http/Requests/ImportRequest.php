<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Spatie\LaravelData\Attributes\Validation\Required;

#[Schema(
    schema: "PasswordBroker_ImportRequest",
    properties: [
        new Property(
            property: "file",
            description: "a XML file - export from KeePass",
            type: "string",
            format: "binary"
        ),
        new Property(property: "master_password", type: "string",),
    ],
)]
class ImportRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'file' => [
                    new Required(),
                    File::types(['xml'])
                ],
            'master_password' => 'required|string|min:1',
        ];
    }
}
