<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Infrastructure\Validation\Rules\EntryFieldTitleDoesNotExistInEntryFields;

/**
 * @property Entry $entry
 */
class EntryFieldStoreRequest extends FormRequest
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
            'type' => Rule::in(array_keys(Field::getRelated())),
            'value_encrypted' => 'required_without:master_password|string|min:1',
            'initialization_vector' => 'required_without:master_password|string|min:1',
            'master_password' => 'required_without:value_encrypted|string|min:1',
            'value' => 'required_without_all:value_encrypted,file|string|min:1',
            'file' => 'required_without_all:value_encrypted,value|file'
        ];
    }

    public function getModel(): string
    {
        return Field::getRelated()[$this->get('type')];
    }
}
