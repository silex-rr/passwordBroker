<?php

namespace PasswordBroker\Application\Http\Requests;

use App\Common\Application\Traits\RequestAllWithCasts;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property GroupName $name
 */
#[Schema(
    schema    : "PasswordBroker_EntryGroupUpdateRequest",
    properties: [
        new Property(property: "name", ref: "#/components/schemas/PasswordBroker_GroupName"),
    ],
)]
class EntryGroupUpdateRequest extends FormRequest
{
    use RequestAllWithCasts;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1',
        ];
    }

    public function getModel(): string
    {
        return EntryGroup::class;
    }

}
