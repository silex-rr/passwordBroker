<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property string $q
 * @property ?EntryGroup $entryGroupInclude
 * @property ?EntryGroup $entryGroupExclude
 * @property ?int perPage
 * @property ?int page
 */
class EntryFieldHistorySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => [
                'string',
                'nullable'
            ],
            'perPage' => [
                'nullable',
                'numeric',
                'min:1',
                'max:100'
            ],
            'page' => [
                'nullable',
                'numeric',
                'min:1'
            ],
            'entryGroupInclude' => [
                'nullable',
                'exists:' . EntryGroup::class . ',entry_group_id'
            ],
            'entryGroupExclude' => [
                'nullable',
                'exists:' . EntryGroup::class . ',entry_group_id'
            ],
        ];
    }

    #[Schema(schema: "PasswordBroker_EntryFieldHistorySearchRequest_q", description: "Search query", type: "string", default: "",)]
    public function getQuery(): string
    {
        return $this->q ?? '';
    }
    public function getEntryGroupInclude(): ?EntryGroup
    {
        if (is_null($this->entryGroupInclude)) {
            return null;
        }
        return EntryGroup::where('entry_group_id', $this->entryGroupInclude)->firstOrFail();
    }

    public function getEntryGroupExclude(): ?EntryGroup
    {
        if (is_null($this->entryGroupExclude)) {
            return null;
        }
        return EntryGroup::where('entry_group_id', $this->entryGroupExclude)->firstOrFail();
    }

    #[Schema(schema: "PasswordBroker_EntryFieldHistorySearchRequest_perPage", type: "integer", default: 15, minimum: 1)]
    public function getPerPage(): int
    {
        return $this->perPage ?? 15;
    }

    #[Schema(schema: "PasswordBroker_EntryFieldHistorySearchRequest_page", type: "integer", default: 1, minimum: 1)]
    public function getPage(): int
    {
        return $this->page ?? 1;
    }
}
