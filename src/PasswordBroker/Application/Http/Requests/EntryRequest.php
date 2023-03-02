<?php

namespace PasswordBroker\Application\Http\Requests;

use App\Common\Application\Traits\RequestAllWithCasts;
use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Infrastructure\Validation\Rules\EntryTitleDoesNotExistInEntryGroup;

/**
 * @property EntryGroup $entryGroup
 */
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
