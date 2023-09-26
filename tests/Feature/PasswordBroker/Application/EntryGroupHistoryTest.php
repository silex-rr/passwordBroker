<?php

namespace PasswordBroker\Application;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use Tests\Feature\PasswordBroker\Application\PasswordHelper;
use Tests\TestCase;

class EntryGroupHistoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use PasswordHelper;

    public function test_a_system_admin_can_see_any_entry_group_history(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var EntryGroup $entryGroupAnother
         * @var User $admin
         * @var User $system_admin
         * @var Entry $entry1
         * @var Entry $entry2
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entryGroupAnother = EntryGroup::factory()->create();
        $system_admin = User::factory()->create(['is_admin' => new IsAdmin(true)]);
        $admin = User::factory()->create();
        $entryTitle = 'entry_' . $this->faker->word;
        $entryFieldTitle = 'field_' . $this->faker->word;
        $entry1 = Entry::factory()->make(['entry_group_id' => null, 'title' => new Title($entryTitle . '_1')]);
        $entry2 = Entry::factory()->make(['entry_group_id' => null, 'title' => new Title($entryTitle . '_2')]);
        $entry3 = Entry::factory()->make(['entry_group_id' => null, 'title' => new Title($entryTitle . '_3')]);
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupAnother);
        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry1, $entryGroup, new EntryValidationHandler()));
        dispatch_sync(new AddEntry($entry2, $entryGroup, new EntryValidationHandler()));
        dispatch_sync(new AddEntry($entry3, $entryGroupAnother, new EntryValidationHandler()));
        /**
         * @var Entry $entry1
         */
        $entry1 = Entry::where('title', $entry1->title)->firstOrFail();
        $password_1_str = $this->faker->password(12, 32);
        $password_1 = $this->getPasswordHelper($admin, $entryGroup, $entry1, $password_1_str, $entryFieldTitle . '_1');
        $password_1_str_new = $password_1_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry1, 'field' => $password_1]),
            [
                'login' => $login_new,
                'value' => $password_1_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Entry $entry2
         */
        $entry2 = Entry::where('title', $entry2->title)->firstOrFail();
        $password_2_str = $this->faker->password(12, 32);
        $password_2 = $this->getPasswordHelper($admin, $entryGroup, $entry2, $password_2_str, $entryFieldTitle . '_2');
        $password_2_str_new = $password_2_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry2, 'field' => $password_2]),
            [
                'login' => $login_new,
                'value' => $password_2_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Entry $entry3
         */
        $entry3 = Entry::where('title', $entry3->title)->firstOrFail();
        $password_another_str = $this->faker->password(12, 32);
        $password_another = $this->getPasswordHelper($admin, $entryGroupAnother, $entry3, $password_another_str, 'test_pass');
        $password_another_str_new = $password_another_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroupAnother, 'entry' => $entry3, 'field' => $password_another]),
            [
                'login' => $login_new,
                'value' => $password_another_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $valid_field_ids = [$entry1->entry_id->getValue(), $entry2->entry_id->getValue()];

        $filedIdValidator = static function(string $field_id) use ($valid_field_ids) {
            return in_array($field_id, $valid_field_ids, true);
        };

        $this->actingAs($system_admin);

        $this->withoutExceptionHandling();
        $this->getJson(route('entryFieldHistorySearch'))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
            => $json->has('data', 6, fn (AssertableJson $history)
                    => $history->where('field.entry_id', $filedIdValidator)
                    ->etc()
                )->etc()
            );

        $q = $password_1->title->getValue();
        $this->getJson(route('entryFieldHistorySearch', ['q' => $q]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
            => $json->has('data', 2, fn (AssertableJson $history)
            => $history->where('field.entry_id', $entry1->entry_id->getValue())
                ->etc()
            )->etc()
            );
    }

    public function test_a_user_can_see_entry_group_history(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var EntryGroup $entryGroupAnother
         * @var User $admin
         * @var Entry $entry1
         * @var Entry $entry2
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entryGroupAnother = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry1 = Entry::factory()->make(['entry_group_id' => null]);
        $entry2 = Entry::factory()->make(['entry_group_id' => null]);
        $entry3 = Entry::factory()->make(['entry_group_id' => null]);
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupAnother);
        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry1, $entryGroup, new EntryValidationHandler()));
        dispatch_sync(new AddEntry($entry2, $entryGroup, new EntryValidationHandler()));
        dispatch_sync(new AddEntry($entry3, $entryGroupAnother, new EntryValidationHandler()));
        /**
         * @var Entry $entry1
         */
        $entry1 = Entry::where('title', $entry1->title)->firstOrFail();
        $password_1_str = $this->faker->password(12, 32);
        $password_1 = $this->getPasswordHelper($admin, $entryGroup, $entry1, $password_1_str);
        $password_1_str_new = $password_1_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry1, 'field' => $password_1]),
            [
                'login' => $login_new,
                'value' => $password_1_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Entry $entry2
         */
        $entry2 = Entry::where('title', $entry2->title)->firstOrFail();
        $password_2_str = $this->faker->password(12, 32);
        $password_2 = $this->getPasswordHelper($admin, $entryGroup, $entry2, $password_2_str);
        $password_2_str_new = $password_2_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry2, 'field' => $password_2]),
            [
                'login' => $login_new,
                'value' => $password_2_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Entry $entry3
         */
        $entry3 = Entry::where('title', $entry3->title)->firstOrFail();
        $password_another_str = $this->faker->password(12, 32);
        $password_another = $this->getPasswordHelper($admin, $entryGroupAnother, $entry3, $password_another_str);
        $password_another_str_new = $password_another_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroupAnother, 'entry' => $entry3, 'field' => $password_another]),
            [
                'login' => $login_new,
                'value' => $password_another_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $valid_field_ids = [$entry1->entry_id->getValue(), $entry2->entry_id->getValue()];

        $filedIdValidator = static function(string $field_id) use ($valid_field_ids) {
            return in_array($field_id, $valid_field_ids, true);
        };

        $this->getJson(route('entryGroupHistory', ['entryGroup' => $entryGroup]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->has(4)->each(fn (AssertableJson $history)
                    => $history->where('field.entry_id', $filedIdValidator)
                        ->etc()
                )->etc()
            );

    }
}
