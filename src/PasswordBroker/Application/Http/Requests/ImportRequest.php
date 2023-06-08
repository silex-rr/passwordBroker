<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Spatie\LaravelData\Attributes\Validation\Required;

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
