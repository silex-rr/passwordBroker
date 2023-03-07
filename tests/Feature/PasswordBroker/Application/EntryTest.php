<?php

namespace Tests\Feature\PasswordBroker\Application;

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
use Tests\TestCase;

class EntryTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;

    public function test_guest_cannot_see_an_entry(): void
    {
        /**
         * @var Entry $entry
         */
        $entry = Entry::factory()->create();

        $this->getJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            )
        )->assertStatus(401);
    }

    public function test_guest_cannot_add_an_entry(): void
    {
        $entryAttributes = Entry::factory()->raw();
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();
        $entriesCountOriginal = $entryGroup->entries()->count();
        $this->postJson(
            route('entryGroupEntries', ['entryGroup'=> $entryGroup->entry_group_id->getValue()]),
            $entryAttributes
        )->assertStatus(401);
        $this->assertEquals($entriesCountOriginal,
            $entryGroup->entries()->count()
        );
    }

    public function test_guest_cannot_update_an_entry(): void
    {
        /**
         * @var Entry $entry
         */
        $entry = Entry::factory()->create();
        $titleOriginal = $entry->title;
        $entryAttributes = $entry->attributesToArray();
        $entryAttributes['title'] = new Title($titleOriginal->getValue() . '_new');

        $this->putJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            $entryAttributes
        )->assertStatus(401);

        /**
         * @var Entry $entryFromDB
         */
        $entryFromDB = Entry::where('entry_id', $entry->entry_id)->firstOrFail();
        $this->assertTrue($entryFromDB->title->equals($titleOriginal));
    }

    public function test_guest_cannot_delete_an_entry(): void
    {
        /**
         * @var Entry $entry
         */
        $entry = Entry::factory()->create();

        $this->deleteJson(route('entryGroupEntry',
            [
                'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]
        ))->assertStatus(401);
    }

    public function test_an_entry_group_admin_can_add_entry_to_the_entry_group(): void
    {
        /**
         * @var User $admin
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

        $this->assertDatabaseHas('entries', $entry_attributes, app(Entry::class)->getConnection()->getName());
        $this->assertEquals(
            1,
            $entryGroup->entries()->where('title', $entry_attributes['title'])->count()
        );

        $this->getJson(route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $entries)
                => $entries->has(1)
                    ->first(fn (AssertableJson $entry)
                    => $entry->where('title', $entry_attributes['title']->getValue())->etc()
                    )
            );
    }

    public function test_an_entry_group_moderator_can_add_entry_to_the_entry_group(): void
    {
        /**
         * @var User $moderator
         * @var EntryGroup $entryGroup
         */
        $moderator = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addModerator($moderator, $this->faker->password(128, 128));
        $this->actingAs($moderator);

        $entry_attributes = Entry::factory()->raw();
        unset($entry_attributes['entry_group_id'], $entry_attributes['entry_id']);
        $this->postJson(
            route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]),
            $entry_attributes
        )->assertStatus(200);

        $this->assertDatabaseHas('entries', $entry_attributes, app(Entry::class)->getConnection()->getName());
        $this->assertEquals(
            1,
            $entryGroup->entries()->where('title', $entry_attributes['title'])->count()
        );

        $this->getJson(route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $entries)
                => $entries->has(1)
                ->first(fn (AssertableJson $entry)
                 => $entry->where('title', $entry_attributes['title']->getValue())->etc()
                )
            );
    }

    public function test_an_entry_group_member_cannot_add_entry_to_the_entry_group(): void
    {
        /**
         * @var User $member
         * @var EntryGroup $entryGroup
         */
        $member = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addMember($member, $this->faker->password(128, 128));
        $this->actingAs($member);

        $entry_attributes = Entry::factory()->raw();
        unset($entry_attributes['entry_group_id'], $entry_attributes['entry_id']);
        $this->postJson(
            route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]),
            $entry_attributes
        )->assertStatus(403);

        $this->assertDatabaseMissing('entries', $entry_attributes, app(Entry::class)->getConnection()->getName());
        $this->assertEquals(
            0,
            $entryGroup->entries()->where('title', $entry_attributes['title'])->count()
        );

        $this->getJson(route('entryGroupEntries', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $entries)
                => $entries->has(0)->etc()
            );
    }

    public function test_an_entry_group_admin_can_update_an_entry_in_the_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         * @var User $admin
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entry = Entry::factory()->make();
        $admin = User::factory()->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);


        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('entry_id', $entry->entry_id)->first();

        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $titleOriginal = $entry->title;
        $titleNew = new Title($entry->title->getValue() . '_new');
        $entryAttributes = $entry->getAttributes();
        $entryAttributes['title'] = $titleNew;

        $this->putJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            $entryAttributes
        )->assertStatus(200);

        $this->assertTrue(
            $entryGroup->entries()->where('title', $titleNew->getValue())->exists()
        );
        $this->assertFalse(
            $entryGroup->entries()->where('title', $titleOriginal->getValue())->exists()
        );
    }

    public function test_an_entry_group_moderator_can_update_an_entry_in_the_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         * @var User $moderator
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entry = Entry::factory()->create();
        $moderator = User::factory()->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($moderator);

        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, $this->faker()->password(128, 128));

        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));

        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $titleOriginal = $entry->title;
        $titleNew = new Title($entry->title->getValue() . '_new');
        $entryAttributes = $entry->getAttributes();
        $entryAttributes['title'] = $titleNew;

        $this->putJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            $entryAttributes
        )->assertStatus(200);

        $this->assertTrue(
            $entryGroup->entries()->where('title', $titleNew->getValue())->exists()
        );
        $this->assertFalse(
            $entryGroup->entries()->where('title', $titleOriginal->getValue())->exists()
        );
    }

    public function test_an_entry_group_member_cannot_update_an_entry_in_the_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         * @var User $member
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $entry = Entry::factory()->create();
        $member = User::factory()->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($member);

        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, $this->faker()->password(128, 128));

        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));

        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $titleOriginal = $entry->title;
        $titleNew = new Title($entry->title->getValue() . '_new');
        $entryAttributes = $entry->getAttributes();
        $entryAttributes['title'] = $titleNew;

        $this->putJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->first()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            $entryAttributes
        )->assertStatus(403);

        $this->assertFalse(
            $entryGroup->entries()->where('title', $titleNew->getValue())->exists()
        );
        $this->assertTrue(
            $entryGroup->entries()->where('title', $titleOriginal->getValue())->exists()
        );
    }

    public function test_an_entry_group_admin_can_delete_an_entry_from_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         */
        $admin = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker->password(128, 128));
        $entry = Entry::factory()->create(['entry_group_id' => $entryGroup->entry_group_id]);

        $this->actingAs($admin);
        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $this->deleteJson(route('entryGroupEntry', [
            'entryGroup' => $entryGroup,
            'entry' => $entry
        ]))->assertStatus(200);

        $this->assertEquals(0,
            $entryGroup->entries()->count()
        );
    }

    public function test_an_entry_group_moderator_can_delete_an_entry_from_the_entry_group(): void
    {
        /**
         * @var User $moderator
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         */
        $moderator = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addModerator($moderator, $this->faker->password(128, 128));
        $entry = Entry::factory()->create(['entry_group_id' => $entryGroup->entry_group_id]);

        $this->actingAs($moderator);
        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $this->deleteJson(route('entryGroupEntry', [
            'entryGroup' => $entryGroup,
            'entry' => $entry
        ]))->assertStatus(200);

        $this->assertEquals(0,
            $entryGroup->entries()->count()
        );
    }

    public function test_an_entry_group_member_cannot_delete_an_entry_from_the_entry_group(): void
    {
        /**
         * @var User $member
         * @var EntryGroup $entryGroup
         * @var Entry $entry
         */
        $member = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addMember($member, $this->faker->password(128, 128));
        $entry = Entry::factory()->create(['entry_group_id' => $entryGroup->entry_group_id]);

        $this->actingAs($member);
        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );

        $this->deleteJson(route('entryGroupEntry', [
            'entryGroup' => $entryGroup,
            'entry' => $entry
        ]))->assertStatus(403);

        $this->assertEquals(1,
            $entryGroup->entries()->count()
        );
    }

    public function test_an_entry_group_admin_can_move_an_entry_to_another_their_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroupSource
         * @var EntryGroup $entryGroupTarget
         * @var Entry $entry
         * @var User $admin
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroupSource, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        $entry = Entry::factory()->create();
        $admin = User::factory()->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupSource);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        dispatch_sync(new AddEntry($entry, $entryGroupSource, new EntryValidationHandler()));

        $this->assertEquals(1,
            $entryGroupSource->entries()->count()
        );

        $this->patchJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->firstOrFail()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue(),
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupSource->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
                ->assertJson(fn (AssertableJson $entries) => $entries->has(0));

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupTarget->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
                ->assertJson(fn (AssertableJson $entries)
                    => $entries->has(1)->first(fn(AssertableJson $entry_json)
                        => $entry_json->where('entry_id', $entry->entry_id->getValue())
                            ->etc()
                        )
                );
    }


    public function test_an_entry_group_moderator_can_move_an_entry_to_another_their_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroupSource
         * @var EntryGroup $entryGroupTarget
         * @var Entry $entry
         * @var User $admin
         * @var User $moderator
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroupSource, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        $entry = Entry::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupSource);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        dispatch_sync(new AddEntry($entry, $entryGroupSource, new EntryValidationHandler()));

        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroupSource, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        $this->actingAs($moderator);

        $this->assertEquals(1,
            $entryGroupSource->entries()->count()
        );

        $this->patchJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->firstOrFail()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue(),
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupSource->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
            ->assertJson(fn (AssertableJson $entries) => $entries->has(0));

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupTarget->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
            ->assertJson(fn (AssertableJson $entries)
                => $entries->has(1)->first(fn(AssertableJson $entry_json)
                    => $entry_json->where('entry_id', $entry->entry_id->getValue())
                        ->etc()
                    )
            );
    }


    public function test_an_entry_group_member_cannot_move_an_entry_to_another_their_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroupSource
         * @var EntryGroup $entryGroupTarget
         * @var Entry $entry
         * @var User $admin
         * @var User $member
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroupSource, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        $entry = Entry::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entryGroupService = app(EntryGroupService::class);
        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupSource);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        dispatch_sync(new AddEntry($entry, $entryGroupSource, new EntryValidationHandler()));

        $entryGroupService->addUserToGroupAsMember($member, $entryGroupSource, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsMember($member, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        $this->actingAs($member);

        $this->assertEquals(1,
            $entryGroupSource->entries()->count()
        );

        $this->patchJson(
            route('entryGroupEntry',
                [
                    'entryGroup' => $entry->entryGroup()->firstOrFail()->entry_group_id->getValue(),
                    'entry' => $entry->entry_id->getValue()
                ]
            ),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue(),
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupTarget->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
            ->assertJson(fn (AssertableJson $entries) => $entries->has(0));

        $this->getJson(route('entryGroupEntries',
            [
                'entryGroup' => $entryGroupSource->entry_group_id->getValue(),
                'entry' => $entry->entry_id->getValue()
            ]))->assertStatus(200)
            ->assertJson(fn (AssertableJson $entries)
                => $entries->has(1)->first(fn(AssertableJson $entry_json)
                    => $entry_json->where('entry_id', $entry->entry_id->getValue())
                        ->etc()
                    )
            );
    }
}
