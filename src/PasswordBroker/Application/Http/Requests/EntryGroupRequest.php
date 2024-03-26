<?php

namespace PasswordBroker\Application\Http\Requests;

use App\Common\Application\Traits\RequestAllWithCasts;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property GroupName $name
 * @property EntryGroupId $parent_entry_group_id
 */
#[Schema(
    schema: "PasswordBroker_EntryGroupRequest",
    properties: [
        new Property(property: "name", ref: "#/components/schemas/PasswordBroker_GroupName"),
        new Property(property: "parent_entry_group_id", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
    ],
)]
class EntryGroupRequest extends FormRequest
{
    use RequestAllWithCasts;
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'name' => 'required|string|min:1',
            'parent_entry_group_id' => 'exists:' . EntryGroup::class . ',entry_group_id'
        ];
    }

    public function getModel(): string
    {
        return EntryGroup::class;
    }

}
