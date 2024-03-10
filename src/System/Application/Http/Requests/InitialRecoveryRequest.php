<?php

namespace System\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * @property string|null $password
 */
class InitialRecoveryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'backupFile' => [
                'required',
                File::types(['zip'])
                    ->min(10)
            ],
            'password' => [
                'nullable'
            ]
        ];
    }
}
