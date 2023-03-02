<?php

namespace Tests\Feature\PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\Passport;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Tests\TestCase;

class EntryGroupTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use EntryGroupRandomAttributes;


    public function test_a_guest_cannot_create_an_entry_group(): void
    {
        $attributes = $this->getEntryGroupRandomAttributes();
        $this->postJson(route('entryGroups'), $attributes)->assertStatus(401);
    }

    public function test_a_guest_cannot_delete_an_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(401);
    }

    public function test_a_guest_does_not_have_access_to_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();

        $this->getJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(401);
    }

    public function test_a_guest_does_not_access_to_view_entry_groups(): void
    {
        EntryGroup::factory()->create();

        $this->getJson(route('entryGroups'))->assertStatus(401);
    }

    public function test_a_user_can_create_an_entry_group(): void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        Passport::actingAs($user);

        $attributes = $this->getEntryGroupRandomAttributes();
        $this->postJson(route('entryGroups'), $attributes)->assertStatus(200);

        $this->assertDatabaseHas('entry_groups', $attributes, app(EntryGroup::class)->getConnection()->getName());
    }

    public function test_a_user_can_see_their_entry_group(): void
    {
        /**
         * @var User $member
         * @var EntryGroup $entryGroup EntryGroup
         */
        $member = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addMember($member, $this->faker->password(128, 128));

        Passport::actingAs($member);

        $this->getJson(route('entryGroups'))->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $entries)
                    => $entries->has(1)->first(
                        fn (AssertableJson $entry)
                            => $entry->where('entry_group_id', $entryGroup->entry_group_id->getValue())->etc()
                )
            );
    }

    public function test_a_user_cannot_see_entry_groups_to_which_they_does_not_belong(): void
    {
        /**
         * @var EntryGroup $entryGroup_1
         * @var EntryGroup $entryGroup_2
         */
        [$entryGroup_1, $entryGroup_2] = EntryGroup::factory()->count(2)->create();
        /**
         * @var User $user_1
         * @var User $user_2
         */
        [$user_1, $user_2] = User::factory()->count(2)->create();

        $entryGroup_1->addAdmin($user_1, $this->faker()->password(128,128));
        $entryGroup_2->addAdmin($user_2, $this->faker()->password(128,128));

        Passport::actingAs($user_1);

        $this->getJson(route('entryGroups'))->assertStatus(200)->assertJson(
            fn(AssertableJson $entries) => $entries->has(1)->first(
                fn (AssertableJson $entry)
                => $entry
                    ->where('entry_group_id', $entryGroup_1->entry_group_id->getValue())
                    ->where('user_id', $user_1->user_id->getValue())
                    ->etc()
            )
        );
    }

    public function test_an_entry_group_admin_can_delete_their_entry_group(): void
    {
        /**
         * @var User $admin
         * @var EntryGroup $entryGroup EntryGroup
         */
        $admin = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker->password(128, 128));

        Passport::actingAs($admin);

        $this->assertEquals(1,
            $admin->adminOf()->count()
        );

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(200);

        $this->assertEquals(0,
            $admin->adminOf()->count()
        );
    }

    public function test_an_moderator_cannot_delete_their_entry_group(): void
    {
        /**
         * @var User $moderator
         * @var EntryGroup $entryGroup EntryGroup
         */
        $moderator = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addModerator($moderator, $this->faker->password(128, 128));

        Passport::actingAs($moderator);

        $this->assertEquals(1,
            $moderator->moderatorOf()->count()
        );

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(403);

        $this->assertEquals(1,
            $moderator->moderatorOf()->count()
        );
    }

    public function test_an_member_cannot_delete_their_entry_group(): void
    {
        /**
         * @var User $member
         * @var EntryGroup $entryGroup EntryGroup
         */
        $member = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addMember($member, $this->faker->password(128, 128));

        Passport::actingAs($member);

        $this->assertEquals(1,
            $member->memberOf()->count()
        );

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(403);

        $this->assertEquals(1,
            $member->memberOf()->count()
        );
    }

    public function test_an_entry_group_admin_cannot_delete_someone_else_groups(): void
    {
        /**
         * @var User $admin
         * @var User $someone_else
         * @var EntryGroup $entryGroup EntryGroup
         */
        [$admin, $someone_else] = User::factory()->count(2)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($someone_else, $this->faker->password(128, 128));

        Passport::actingAs($admin);

        $this->assertDatabaseHas($entryGroup->getTable(),
            $entryGroup->getAttributes()
        );

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(403);

        $this->assertDatabaseHas($entryGroup->getTable(),
            $entryGroup->getAttributes()
        );
    }

    public function test_an_entry_group_admin_can_move_the_entry_group_to_another_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var EntryGroup $entryGroupTarget
         * @var User $admin
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroup, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        $admin = User::factory()->create();
        $entryGroupService = app(EntryGroupService::class);

        Passport::actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $this->patchJson(
            route('entryGroup', ['entryGroup' => $entryGroup]),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue()
            ]
        )->assertStatus(200);

        /**
         * @var EntryGroup $entryGroupFromDB
         */
        $entryGroupFromDB = EntryGroup::where('entry_group_id', $entryGroup->entry_group_id->getValue())->firstOrFail();

        $this->assertTrue($entryGroupTarget->entry_group_id->equals($entryGroupFromDB->parentEntryGroup()->firstOrFail()->entry_group_id));

        $this->withoutExceptionHandling();
        $this->patchJson(
            route('entryGroup', ['entryGroup' => $entryGroup]),
            [
                'entryGroupTarget' => null
            ]
        )->assertStatus(200);

        /**
         * @var EntryGroup $entryGroupFromDB
         */
        $entryGroupFromDB = EntryGroup::where('entry_group_id', $entryGroup->entry_group_id->getValue())->firstOrFail();

        $this->assertNull($entryGroupFromDB->parentEntryGroup()->first());
    }


    public function test_an_entry_group_moderator_cannot_move_the_entry_group_to_another_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var EntryGroup $entryGroupTarget
         * @var User $admin
         * @var User $moderator
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroup, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entryGroupService = app(EntryGroupService::class);

        Passport::actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        Passport::actingAs($moderator);

        $this->patchJson(
            route('entryGroup', ['entryGroup' => $entryGroup]),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue()
            ]
        )->assertStatus(403);

        /**
         * @var EntryGroup $entryGroupFromDB
         */
        $entryGroupFromDB = EntryGroup::where('entry_group_id', $entryGroup->entry_group_id->getValue())->firstOrFail();

        $this->assertTrue($entryGroupFromDB->parentEntryGroup()->doesntExist());
    }


    public function test_an_entry_group_member_cannot_move_the_entry_group_to_another_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var EntryGroup $entryGroupTarget
         * @var User $admin
         * @var User $member
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroup, $entryGroupTarget] = EntryGroup::factory()->count(2)->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entryGroupService = app(EntryGroupService::class);

        Passport::actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsMember($member, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        Passport::actingAs($member);

        $this->patchJson(
            route('entryGroup', ['entryGroup' => $entryGroup]),
            [
                'entryGroupTarget' => $entryGroupTarget->entry_group_id->getValue()
            ]
        )->assertStatus(403);

        /**
         * @var EntryGroup $entryGroupFromDB
         */
        $entryGroupFromDB = EntryGroup::where('entry_group_id', $entryGroup->entry_group_id->getValue())->firstOrFail();

        $this->assertTrue($entryGroupFromDB->parentEntryGroup()->doesntExist());
    }
}
