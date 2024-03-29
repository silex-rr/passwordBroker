<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property EntryGroup $entryGroup
 */
#[Schema(
    schema: "PasswordBroker_EntryGroupMoveRequest",
    properties: [
        new Property("entryGroupTarget", ref: "#/components/schemas/PasswordBroker_EntryGroupId", nullable: true),
    ],
)]
class EntryGroupMoveRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }
    public function rules() : array
    {
        return [
            'entryGroupTarget' => [
                'nullable',
                'exists:' . EntryGroup::class . ',entry_group_id'
            ]
        ];
    }

    public function entryGroupTarget(): ?EntryGroup
    {
        $entry_group_id = $this->get('entryGroupTarget');
        if (is_null($entry_group_id)) {
            return null;
        }
        return EntryGroup::where('entry_group_id', $entry_group_id)->firstOrFail();
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
