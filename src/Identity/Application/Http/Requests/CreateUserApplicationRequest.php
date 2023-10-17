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
            'userApplicationId' => [
                'nullable',
                'uuid'
            ]
        ];
    }

    public function userApplicationId(): ?string
    {
        return $this->get('userApplicationId');
    }
}
