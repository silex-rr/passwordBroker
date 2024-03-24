<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Infrastructure\Validation\Rules\EntryFieldTitleDoesNotExistInEntryFields;

/**
 * @property Entry $entry
 * @property Field $field
 */
#[Schema(
    schema: "PasswordBroker_EntryFieldUpdateRequest",
    properties: [
        new Property(property: "title", ref: "#/components/schemas/PasswordBroker_FieldTitle", nullable: true),
        new Property(property: "login", ref: "#/components/schemas/PasswordBroker_Login", nullable: true),
        new Property(property: "value_encrypted", ref: "#/components/schemas/PasswordBroker_ValueEncrypted", nullable: true),
        new Property(property: "initialization_vector", ref: "#/components/schemas/PasswordBroker_InitializationVector", nullable: true),
        new Property(property: "master_password", type: "string", nullable: true),
        new Property(property: "value", type: "string", nullable: true),
    ],
)]
class EntryFieldUpdateRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'min:1',
                new EntryFieldTitleDoesNotExistInEntryFields($this->entry, $this->field),
            ],
            'login' => "string|nullable|min:1",
            'value_encrypted' => 'required_with:initialization_vector|string|min:1',
            'initialization_vector' => 'required_with:value_encrypted|string|min:1',
            'master_password' => 'required_with:value|string|min:1',
            'value' => 'required_with:master_password|string|min:1',
        ];
    }

    public function getModel(): string
    {

        return $this->field::class;
    }
}
