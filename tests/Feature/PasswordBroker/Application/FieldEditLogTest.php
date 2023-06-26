<?php

namespace PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Events\FieldUpdated;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\FieldEditLog;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use Tests\Feature\PasswordBroker\Application\PasswordHelper;
use Tests\TestCase;

class FieldEditLogTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use PasswordHelper;
    public function test_a_user_can_see_field_edit_logs(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make(['entry_group_id' => null]);
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);
        $password_str_new = $password_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
            [
                'login' => $login_new,
                'value' => $password_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);


        /**
         * @var Password $password_updated
         */
        $password_updated = Password::where('field_id', $password->field_id)->firstOrFail();
        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        $password_str_updated = $entryGroupService->decryptField($password_updated, UserFactory::MASTER_PASSWORD);

        $this->assertEquals($password_str_new, $password_str_updated);
        $fieldEditLogsQuery = $password_updated->fieldEditLogs()->where('event_type', FieldUpdated::EVENT_TYPE);
        $this->assertEquals(1, $fieldEditLogsQuery->count());
        /**
         * @var FieldEditLog $fieldEditLog
         */
        $fieldEditLog = $fieldEditLogsQuery->first();
        $this->assertEquals($fieldEditLog->login->getValue(), $login_new);
        $this->assertEquals($password_str_new,
            $entryGroupService->decryptFieldEditLog($fieldEditLog,
                UserFactory::MASTER_PASSWORD));

        $eventCounter = static function (string $event_type) {
            static $event_types = [
                'created' => 1,
                'updated' => 1
            ];

            return --$event_types[$event_type] >= 0;
        };

        $this->getJson(route('entryFieldHistory',
                [
                    'entryGroup' => $entryGroup,
                    'entry' => $entry,
                    'field' => $password
                ]
            )
        )
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $logs)
                => $logs->has(2)->each(fn (AssertableJson $log)
                    => $log->where('event_type', $eventCounter)->etc()
                )->etc()
            );
    }
}
