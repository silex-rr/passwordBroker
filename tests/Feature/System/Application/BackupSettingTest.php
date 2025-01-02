<?php

namespace Tests\Feature\System\Application;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use System\Domain\Settings\Models\BackupSetting;
use Tests\TestCase;

class BackupSettingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_an_system_administrator_can_edit_backup_settings(): void
    {
        $user = User::factory()->systemAdministrator()->create();
        $this->actingAs($user);
        $route_name = 'system_backup_setting';
        $route = route($route_name, ['backupSetting' => BackupSetting::TYPE]);

        $this->getJson($route)->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('key', BackupSetting::TYPE)
                    ->has('schedule', 0)
                    ->where('enable', false)
                    ->etc()
            );

        $archive_password = $this->faker->password();
        $email = $this->faker->email();

        $data = [
            'key' => BackupSetting::TYPE,
            'schedule' => [8, 12, 20],
            'archive_password' => $archive_password,
            'enable' => true,
            'email_enable' => false,
            'email' => $email,
        ];

        $this->postJson($route, $data)
            ->assertStatus(200);

        $validateSchedule = static fn ($el) => in_array($el, $data['schedule'], false);

        $this->getJson($route)->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('key', BackupSetting::TYPE)
                    ->where('enable', true)
                    ->has('schedule', 3)
                    ->where('schedule.0', $validateSchedule)
                    ->where('schedule.1', $validateSchedule)
                    ->where('schedule.2', $validateSchedule)
                    ->where('archive_password', $archive_password)
                    ->where('email_enable', false)
                    ->where('email', $email)
                    ->etc()
            );
    }

}
