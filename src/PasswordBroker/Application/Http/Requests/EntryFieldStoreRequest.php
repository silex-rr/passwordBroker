<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Link;
use PasswordBroker\Domain\Entry\Models\Fields\Note;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Models\Fields\TOTP;
use PasswordBroker\Infrastructure\Validation\Rules\EntryFieldTitleDoesNotExistInEntryFields;

/**
 * @property Entry $entry
 */
#[Schema(
    schema: "PasswordBroker_EntryFieldStoreRequest",
    properties: [
        new Property(property: "title", ref: "#/components/schemas/PasswordBroker_FieldTitle", nullable: true),
        new Property(property: "type", type: "string", enum: [File::TYPE, Link::TYPE, Note::TYPE, Password::TYPE, TOTP::TYPE]),
        new Property(property: "login", ref: "#/components/schemas/PasswordBroker_Login", nullable: true),
        new Property(property: "totp_hash_algorithm", ref: "#/components/schemas/PasswordBroker_TOTPHashAlgorithm", nullable: true),
        new Property(property: "totp_timeout", ref: "#/components/schemas/PasswordBroker_TOPTTimeout", nullable: true),
        new Property(property: "value_encrypted", ref: "#/components/schemas/PasswordBroker_ValueEncrypted", nullable: true),
        new Property(property: "initialization_vector", ref: "#/components/schemas/PasswordBroker_InitializationVector", nullable: true),
        new Property(property: "master_password", type: "string", nullable: true),
        new Property(property: "value", type: "string", nullable: true),
        new Property(property: "file", ref: "#/components/schemas/PasswordBroker_File", nullable: true),
    ],
)]
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
            'login' => [new RequiredIf(fn () => $this->get('type') === Password::TYPE), 'string', 'min:1'],
            'totp_hash_algorithm' => [
                'nullable',
                'string',
                Rule::when($this->has('totp_hash_algorithm'), Rule::in(TOTPHashAlgorithm::toArray()))
            ],
            'totp_timeout' => 'integer|between:1,999',
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
