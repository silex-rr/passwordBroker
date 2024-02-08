<?php

namespace System\Application\Observers;

use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use System\Domain\Settings\Models\Setting;

class SettingObserver
{
    public function creating(Setting $setting): void
    {
        $setting->setting_id;
    }
    public function saving(Setting $setting): void
    {
        $this->updating($setting);
    }

    public function updating(Setting $setting): void
    {
        $setting->setting_id;
        $setting->packData();
        $setting->unpackData();
        if (app()->runningInConsole()) {
            return;
        }
        /**
         * @var User $user
         */
        $user = Auth::user();
        $setting->updated_by = $user->user_id;
    }

    public function retrieved(Setting $setting): void
    {
        $setting->unpackData();
    }
}
