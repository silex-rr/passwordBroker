<?php

namespace PasswordBroker\Application\Http\Requests;

use App\Common\Application\Traits\RequestAllWithCasts;
use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

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
