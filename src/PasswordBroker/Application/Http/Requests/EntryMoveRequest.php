<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property EntryGroup $entryGroup
 */
class EntryMoveRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }
    public function rules() : array
    {
        return [
            'master_password' => 'required|string|min:1',
            'entryGroupTarget' => [
                'required',
                'exists:' . EntryGroup::class . ',entry_group_id'
            ]
        ];
    }

    public function entryGroupTarget(): EntryGroup
    {
        return EntryGroup::where('entry_group_id', $this->get('entryGroupTarget'))->firstOrFail();
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
