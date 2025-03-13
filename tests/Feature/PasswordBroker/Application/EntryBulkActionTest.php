<?php

namespace Feature\PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\PasswordBroker\Application\PasswordHelper;
use Tests\TestCase;

class EntryBulkActionTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;
    use PasswordHelper;

    public function test_an_user_can_bulk_delete_entries(): void
    {
        /**
         * @var User       $admin
         * @var EntryGroup $entryGroup
         */
        $admin = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker->password(128, 128));

        $this->actingAs($admin);

        $entry_attributes = Entry::factory()->raw();
        unset($entry_attributes['entry_group_id'], $entry_attributes['entry_id']);
        $this->postJson(
            route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]),
            $entry_attributes
        )->assertStatus(200);

        $this->assertDatabaseHas(Entry::class, $entry_attributes, app(Entry::class)->getConnection()->getName());
        $this->assertEquals(
            1,
            $entryGroup->entries()->where('title', $entry_attributes['title'])->count()
        );

        $entry = $entryGroup->entries()->where('title', $entry_attributes['title'])->firstOrFail();

        $this->getJson(route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $entries) => $entries->has(1)
                ->first(fn(AssertableJson $entry) => $entry->where('title',
                    $entry_attributes['title']->getValue())->etc()
                )
            );

        $this->postJson(route('entryGroupEntriesBulkDestroy',
            [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]
        ), [
            'entries' => [
                $entry->entry_id->getValue(),
            ],
        ])->assertStatus(Response::HTTP_NO_CONTENT);

        $entry_attributes['deleted_at'] = null;

        $this->assertDatabaseMissing(Entry::class, $entry_attributes, app(Entry::class)->getConnection()->getName());
        $this->assertEquals(
            0,
            $entryGroup->entries()->where('title', $entry_attributes['title'])->count()
        );
    }

    public function test_an_user_can_bulk_move_entries(): void
    {
        /**
         * @var EntryGroup        $entryGroup
         * @var User              $admin
         * @var Entry             $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entryGroupTarget = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $fields) => $fields->has(1)->first(
                    fn(AssertableJson $field) => $field->where('field_id', $password->field_id->getValue())
                        ->where('type', $password->getType())
                        ->etc()
                )
            );

        $this->postJson(route('entryGroupEntriesBulkMove',
            [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]
        ), [
            'master_password' => UserFactory::MASTER_PASSWORD,
            'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue(),
            'entries' => [
                $entry->entry_id->getValue(),
            ],
        ])->assertStatus(Response::HTTP_NO_CONTENT);

        $this->getJson(route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $entries) => $entries->has(0)
            );

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroupTarget, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $fields) => $fields->has(1)
            );
    }
}
