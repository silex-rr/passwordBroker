<?php

namespace Tests\Feature\PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use Tests\TestCase;

class EntryGroupUserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use EntryGroupRandomAttributes;

    public function test_an_entry_group_admin_can_add_an_admin_to_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $new_admin
         */
        [$admin, $new_admin] = User::factory()->count(2)->create();
        $entryGroupRandomAttributes = $this->getEntryGroupRandomAttributes();

        $this->actingAs($admin);
        $this->postJson(route('entryGroups'), $entryGroupRandomAttributes)->assertStatus(200);
        $this->assertDatabaseHas(app(EntryGroup::class)->getTableFullName(), $entryGroupRandomAttributes);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $admin->adminOf()->with('entryGroup')->firstOrFail()
            ->entryGroup()->where('name', $entryGroupRandomAttributes['name'])->firstOrFail();

        $this->assertInstanceOf(EntryGroup::class, $entryGroup);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_admin->user_id->getValue(),
                'role' => Admin::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ])->assertStatus(200);
        $this->assertEquals(0,
            Member::where('user_id', $new_admin->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(1,
            Admin::where('user_id', $new_admin->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(0,
            Moderator::where('user_id', $new_admin->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
    }

    public function test_an_entry_group_admin_can_add_a_moderator_to_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $new_moderator
         */
        [$admin, $new_moderator] = User::factory()->count(2)->create();
        $entryGroupRandomAttributes = $this->getEntryGroupRandomAttributes();

        $this->actingAs($admin);
        $this->postJson(route('entryGroups'), $entryGroupRandomAttributes)->assertStatus(200);
        $this->assertDatabaseHas(app(EntryGroup::class)->getTableFullName(), $entryGroupRandomAttributes);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $admin->adminOf()->with('entryGroup')->firstOrFail()
            ->entryGroup()->where('name', $entryGroupRandomAttributes['name'])->firstOrFail();

        $this->assertInstanceOf(EntryGroup::class, $entryGroup);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_moderator->user_id->getValue(),
                'role' => Moderator::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ])->assertStatus(200);
        $this->assertEquals(0,
            Member::where('user_id', $new_moderator->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(0,
            Admin::where('user_id', $new_moderator->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(1,
            Moderator::where('user_id', $new_moderator->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
    }

    public function test_an_entry_group_admin_can_add_a_member_to_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $new_member
         */
        [$admin, $new_member] = User::factory()->count(2)->create();
        $entryGroupRandomAttributes = $this->getEntryGroupRandomAttributes();

        $this->actingAs($admin);
        $this->postJson(route('entryGroups'), $entryGroupRandomAttributes)->assertStatus(200);
        $this->assertDatabaseHas(app(EntryGroup::class)->getTableFullName(), $entryGroupRandomAttributes);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $admin->adminOf()->with('entryGroup')->firstOrFail()
            ->entryGroup()->where('name', $entryGroupRandomAttributes['name'])->firstOrFail();

        $this->assertInstanceOf(EntryGroup::class, $entryGroup);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_member->user_id->getValue(),
                'role' => Member::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ])->assertStatus(200);
        $this->assertEquals(1,
            Member::where('user_id', $new_member->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(0,
            Admin::where('user_id', $new_member->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
        $this->assertEquals(0,
            Moderator::where('user_id', $new_member->user_id->getValue())
                ->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
    }

    public function test_an_entry_group_admin_can_delete_an_admin_from_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $second_admin
         * @var EntryGroup $entryGroup
         */
        [$admin, $second_admin] = User::factory()->count(2)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker()->password(128,128));
        $entryGroup->addAdmin($second_admin, $this->faker()->password(128,128));

        $this->assertEquals(2, $entryGroup->users()->count());

        $this->actingAs($admin);

        $this->deleteJson(route('entryGroupUser',
            ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $second_admin->user_id->getValue()])
        )->assertStatus(200);

        $this->assertEquals(1, $entryGroup->users()->count());
    }

    public function test_an_entry_group_admin_can_delete_a_moderator_from_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $moderator
         * @var EntryGroup $entryGroup
         */
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker()->password(128,128));
        $entryGroup->addModerator($moderator, $this->faker()->password(128,128));

        $this->assertEquals(2, $entryGroup->users()->count());

        $this->actingAs($admin);

        $this->deleteJson(route('entryGroupUser',
            ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $moderator->user_id->getValue()])
        )->assertStatus(200);

        $this->assertEquals(1, $entryGroup->users()->count());
    }

    public function test_an_entry_group_admin_can_delete_a_member_from_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $member
         * @var EntryGroup $entryGroup
         */
        [$admin, $member] = User::factory()->count(2)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker()->password(128,128));
        $entryGroup->addMember($member, $this->faker()->password(128,128));

        $this->assertEquals(2, $entryGroup->users()->count());

        $this->actingAs($admin);

        $this->deleteJson(route('entryGroupUser',
            ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $member->user_id->getValue()])
        )->assertStatus(200);

        $this->assertEquals(1, $entryGroup->users()->count());
    }

    public function test_an_entry_group_admin_cannot_delete_the_last_admin_from_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var EntryGroup $entryGroup
         */
        $admin = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker()->password(128,128));

        $this->assertEquals(1, $entryGroup->admins()->count());

        $this->actingAs($admin);

        $this->deleteJson(route('entryGroupUser',
            ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $admin->user_id->getValue()])
        )->assertStatus(409);

        $this->assertEquals(1, $entryGroup->admins()->count());
    }

    public function test_an_entry_group_moderator_cannot_add_a_user_with_any_role_to_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $moderator
         * @var User $new_admin
         * @var User $new_moderator
         * @var User $new_member
         */
        [$admin, $moderator, $new_admin, $new_moderator, $new_member] = User::factory()->count(5)->create();
        $entryGroupRandomAttributes = $this->getEntryGroupRandomAttributes();

        $this->actingAs($admin);
        $this->postJson(route('entryGroups'), $entryGroupRandomAttributes)->assertStatus(200);
        $this->assertDatabaseHas(app(EntryGroup::class)->getTableFullName(), $entryGroupRandomAttributes);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $admin->adminOf()->with('entryGroup')->firstOrFail()
            ->entryGroup()->where('name', $entryGroupRandomAttributes['name'])->firstOrFail();

        app(EntryGroupService::class)->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);

        $this->actingAs($moderator);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_admin->user_id->getValue(),
                'role' => Admin::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_moderator->user_id->getValue(),
                'role' => Moderator::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_member->user_id->getValue(),
                'role' => Member::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->assertEquals(0,
            $entryGroup->users()->whereIn('user_id', [
                $new_admin->user_id->getValue(),
                $new_moderator->user_id->getValue(),
                $new_member->user_id->getValue(),
            ])->count()
        );
    }

    public function test_an_entry_group_moderator_cannot_delete_a_user_with_any_role_to_the_entry_group(): void
    {
        /**
         * @var User $moderator
         * @var User $target_admin
         * @var User $target_moderator
         * @var User $target_member
         * @var EntryGroup $entryGroup
         */
        [$moderator, $target_admin, $target_moderator, $target_member] = User::factory()->count(4)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addModerator($moderator, $this->faker()->password(128,128));
        $entryGroup->addAdmin($target_admin, $this->faker()->password(128,128));
        $entryGroup->addModerator($target_moderator, $this->faker()->password(128,128));
        $entryGroup->addMember($target_member, $this->faker()->password(128,128));

        $this->assertEquals(4, $entryGroup->users()->count());

        $this->actingAs($moderator);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_admin->user_id->getValue()])
        )->assertStatus(403);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_moderator->user_id->getValue()])
        )->assertStatus(403);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_member->user_id->getValue()])
        )->assertStatus(403);

        $this->assertEquals(4, $entryGroup->users()->count());
    }

    public function test_an_entry_group_member_cannot_add_a_user_with_any_role_to_the_entry_group(): void
    {
        /**
         * @var User $admin
         * @var User $member
         * @var User $new_admin
         * @var User $new_moderator
         * @var User $new_member
         */
        [$admin, $member, $new_admin, $new_moderator, $new_member] = User::factory()->count(5)->create();
        $entryGroupRandomAttributes = $this->getEntryGroupRandomAttributes();

        $this->actingAs($admin);
        $this->postJson(route('entryGroups'), $entryGroupRandomAttributes)->assertStatus(200);
        $this->assertDatabaseHas(app(EntryGroup::class)->getTableFullName(), $entryGroupRandomAttributes);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $admin->adminOf()->with('entryGroup')->firstOrFail()
            ->entryGroup()->where('name', $entryGroupRandomAttributes['name'])->firstOrFail();

        app(EntryGroupService::class)->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);

        $this->actingAs($member);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_admin->user_id->getValue(),
                'role' => Admin::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_moderator->user_id->getValue(),
                'role' => Moderator::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->postJson(
            route('entryGroupUsers', [
                'entryGroup' => $entryGroup->entry_group_id->getValue(),
            ]),
            [
                'target_user_id' => $new_member->user_id->getValue(),
                'role' => Member::ROLE_NAME,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->assertEquals(0,
            $entryGroup->users()->whereIn('user_id', [
                $new_admin->user_id->getValue(),
                $new_moderator->user_id->getValue(),
                $new_member->user_id->getValue(),
            ])->count()
        );
    }

    public function test_an_entry_group_member_cannot_delete_a_user_with_any_role_to_the_entry_group(): void
    {
        /**
         * @var User $member
         * @var User $target_admin
         * @var User $target_moderator
         * @var User $target_member
         * @var EntryGroup $entryGroup
         */
        [$member, $target_admin, $target_moderator, $target_member] = User::factory()->count(4)->create();
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addMember($member, $this->faker()->password(128,128));
        $entryGroup->addAdmin($target_admin, $this->faker()->password(128,128));
        $entryGroup->addModerator($target_moderator, $this->faker()->password(128,128));
        $entryGroup->addMember($target_member, $this->faker()->password(128,128));

        $this->assertEquals(4, $entryGroup->users()->count());

        $this->actingAs($member);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_admin->user_id->getValue()])
        )->assertStatus(403);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_moderator->user_id->getValue()])
        )->assertStatus(403);

        $this->deleteJson(route('entryGroupUser',
                ['entryGroup' => $entryGroup->entry_group_id->getValue(), 'user' => $target_member->user_id->getValue()])
        )->assertStatus(403);

        $this->assertEquals(4, $entryGroup->users()->count());
    }

//    /**
//     * Combines SQL and its bindings
//     *
//     * @param \Eloquent $query
//     * @return string
//     */
//    public static function getEloquentSqlWithBindings($query)
//    {
//        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
//            return is_numeric($binding) ? $binding : "'{$binding}'";
//        })->toArray());
//    }
}
