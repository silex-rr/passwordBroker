<?php

namespace PasswordBroker\Application\Http\Requests;

use App\Common\Application\Traits\RequestAllWithCasts;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Rules\EntryTitleDoesNotExistInEntryGroup;

/**
 * @property EntryGroup $entryGroup
 */
#[Schema(
    schema: "PasswordBroker_EntryRequest",
    properties: [
        new Property(property: "title", type: "string", nullable: false,),
    ],
)]
class EntryRequest extends FormRequest
{
    use RequestAllWithCasts;
    public function authorize() : bool
    {
        return true;//$this->user()->can('create', Entry::class, $this->entryGroup);
    }
    public function rules() : array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:1',
                new EntryTitleDoesNotExistInEntryGroup($this->entryGroup)
            ]
        ];
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
