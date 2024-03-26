<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Schema;

/**
 * @property string $q
 * @property ?int perPage
 * @property ?int page
 */
class EntrySearchRequest extends FormRequest
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
        ];
    }

    #[Schema(schema: "PasswordBroker_EntrySearchRequest_q", description: "Search query", type: "string", default: "",)]
    public function getQuery(): string
    {
        return $this->q ?? '';
    }

    #[Schema(schema: "PasswordBroker_EntrySearchRequest_perPage", type: "integer", default: 15, minimum: 1)]
    public function getPerPage(): int
    {
        return $this->perPage ?? 15;
    }

    #[Schema(schema: "PasswordBroker_EntrySearchRequest_page", type: "integer", default: 1, minimum: 1)]
    public function getPage(): int
    {
        return $this->page ?? 1;
    }
}
