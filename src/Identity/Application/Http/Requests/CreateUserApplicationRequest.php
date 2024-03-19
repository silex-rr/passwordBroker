<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_CreateUserApplicationRequest",
    properties: [
        new Property(property: "clientId", type: "string", format: 'uuid', nullable: true)
    ],
    type: "object"
)]
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
