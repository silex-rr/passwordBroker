<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Infrastructure\Validation\Rules\EntryFieldTitleDoesNotExistInEntryFields;

/**
 * @property Entry $entry
 * @property Field $field
 */
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
                new EntryFieldTitleDoesNotExistInEntryFields($this->entry),
            ],
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
