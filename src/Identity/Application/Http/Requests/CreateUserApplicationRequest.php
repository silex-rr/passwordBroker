<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clientId' => [
                'nullable',
                'uuid'
            ]
        ];
    }

    public function clientId(): ?string
    {
        return $this->get('clientId');
    }
}
