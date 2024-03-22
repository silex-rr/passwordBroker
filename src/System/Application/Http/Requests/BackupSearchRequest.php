<?php

namespace System\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\QueryParameter;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\EntryGroup;

/**
 * @property ?string $q
 * @property ?int perPage
 * @property ?int page
 */

#[Schema(
    schema: "System_BackupSearchRequest",
    properties: [
        new Property(property: "q", description: "query search by backup name", type: "string", example: "part_of_backup_name", nullable: true),
        new Property(property: "perPage", type: "integer", example: 15, nullable: true),
        new Property(property: "page", type: "integer", example: 1, nullable: true),
    ],
    type: "object",
)]
class BackupSearchRequest extends FormRequest
{
    #[Schema(schema: "System_BackupSearchRequest_q", type: "string", nullable: true)]
    public ?string $q = null;
    #[Schema(schema: "System_BackupSearchRequest_perPage", type: "integer", minimum: 1, example: 15, nullable: true)]
    public ?int $perPage = null;
    #[Schema(schema: "System_BackupSearchRequest_page", type: "integer", maximum: 100, minimum: 1, example: 1, nullable: true)]
    public ?int $page = null;


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
            ]
        ];
    }

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

    public function getPerPage(): int
    {
        return $this->perPage ?? 15;
    }
    public function getPage(): int
    {
        return $this->page ?? 1;
    }


}
