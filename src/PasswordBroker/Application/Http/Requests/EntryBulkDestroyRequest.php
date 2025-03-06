<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

#[Schema(
    schema: "PasswordBroker_EntryBulkDestroyRequest",
    properties: [
        new Property(property: "entries",
                     type: "array",
                     items: new Items(ref: "#/components/schemas/PasswordBroker_EntryId")),
    ],
)]
class EntryBulkDestroyRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }
    public function rules() : array
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $this->route('entryGroup');
        return [
            'entries' => 'required|array',
            'entries.*' => [
                function ($attribute, $value, $fail) use ($entryGroup) {
                    if (!Entry::where('entry_id', $value)->where('entry_group_id', $entryGroup->entry_group_id)->exists()) {
                        $fail("The selected entry ({$value}) does not belong to the specified entry group.");
                    }
                },
            ]
        ];
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
