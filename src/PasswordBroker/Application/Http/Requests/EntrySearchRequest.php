<?php

namespace PasswordBroker\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

    public function getQuery(): string
    {
        return $this->q ?? '';
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
