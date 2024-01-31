<?php

namespace System\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use System\Infrastructure\Validation\Rule\ArrayOfHoursRule;

/**
 * @property int[] $schedule
 * @property bool $enable
 * @property bool $email_enable
 * @property string|null $email
 * @property string|null $archive_password
 */
class BackupSettingRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }
    public function rules() : array
    {
        return [
            'schedule' => [
                'required',
                new ArrayOfHoursRule(),
            ],
            'enable' => [
                'required',
                'boolean',
            ],
            'email_enable' => [
                'required',
                'boolean',
            ],
            'email' => [
                'email',
                'nullable',
            ],
            'archive_password' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
