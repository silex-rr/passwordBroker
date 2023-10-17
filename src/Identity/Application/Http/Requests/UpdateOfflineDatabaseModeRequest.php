<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfflineDatabaseModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'boolean'
            ]
        ];
    }

    public function status(): bool
    {
        return (bool)$this->get('status');
    }
}
