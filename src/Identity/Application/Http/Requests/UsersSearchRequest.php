<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property string $q
 * @property ?EntryGroup $entryGroupInclude
 * @property ?EntryGroup $entryGroupExclude
 * @property ?int perPage
 * @property ?int page
 */
class UsersSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'q' => [
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

    #[Schema(schema: "Identity_UsersSearchRequest_q", type: "string", nullable: true)]
    public function getQuery(): string
    {
        return $this->q ?? '';
    }

    #[Schema(schema: "Identity_UsersSearchRequest_entryGroupInclude", type: "string", format: "uuid", nullable: true)]
    public function getEntryGroupInclude(): ?EntryGroup
    {
        if (is_null($this->entryGroupInclude)) {
            return null;
        }
        return EntryGroup::where('entry_group_id', $this->entryGroupInclude)->firstOrFail();
    }

    #[Schema(schema: "Identity_UsersSearchRequest_entryGroupExclude", type: "string", format: "uuid", nullable: true)]
    public function getEntryGroupExclude(): ?EntryGroup
    {
        if (is_null($this->entryGroupExclude)) {
            return null;
        }
        return EntryGroup::where('entry_group_id', $this->entryGroupExclude)->firstOrFail();
    }

    #[Schema(schema: "Identity_UsersSearchRequest_perPage", type: "integer", minimum: 1, example: 15, nullable: true)]
    public function getPerPage(): int
    {
        return $this->perPage ?? 15;
    }

    #[Schema(schema: "Identity_UsersSearchRequest_page", type: "integer", maximum: 100, minimum: 1, example: 1, nullable: true)]
    public function getPage(): int
    {
        return $this->page ?? 1;
    }


}
