<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_UpdateOfflineDatabaseModeRequest",
    properties: [
        new Property(property: "status", type: "boolean")
    ],
    type: "object"
)]
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
