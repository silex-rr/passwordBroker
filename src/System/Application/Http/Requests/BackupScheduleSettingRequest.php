<?php

namespace System\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use System\Infrastructure\Validation\Rule\ArrayOfHoursRule;

/**
 * @property int[] $schedule
 */
class BackupScheduleSettingRequest extends FormRequest
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
                new ArrayOfHoursRule()
            ]
        ];
    }

    public function getModel(): string
    {
        return Entry::class;
    }
}
