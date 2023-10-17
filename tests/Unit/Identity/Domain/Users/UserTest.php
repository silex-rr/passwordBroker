<?php

namespace Tests\Unit\Identity\Domain\Users;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    public function test_a_user_can_be_a_system_administrator(): void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $user->is_admin = IsAdmin::fromNative(true);
        $user->save();

        /**
         * @var User $userDb
         */
        $userDb = User::where('user_id', $user->user_id->getValue())->first();
        $this->assertTrue($userDb->is_admin->getValue());
    }

    public function test_a_user_can_be_an_administrator_of_an_entry_group(): void
    {
        /**
         * @var User $user
         * @var EntryGroup $entryGroup
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();

        $this->assertInstanceOf(HasMany::class, $user->adminOf());

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

//        $user->addAsAdminOf($entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($user, $entryGroup);

        $this->assertEquals(
            1,
            $user->adminOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $user->memberOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $user->moderatorOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

    }

    public function test_a_user_can_be_a_moderator_of_an_entry_group(): void
    {
        /**
         * @var User $user
         * @var EntryGroup $entryGroup
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->moderatorOf());

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

//        $user->addAsModeratorOf($entryGroup);
        $entryGroupService->addUserToGroupAsModerator($user, $entryGroup);

        $this->assertEquals(
            0,
            $user->adminOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $user->memberOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            1,
            $user->moderatorOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
    }

    public function test_a_user_can_be_a_member_of_an_entry_group(): void
    {
        /**
         * @var User $user
         * @var EntryGroup $entryGroup
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->memberOf());

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

//        $user->addAsMemberOf($entryGroup);
        $entryGroupService->addUserToGroupAsMember($user, $entryGroup);

        $this->assertEquals(
            0,
            $user->adminOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            1,
            $user->memberOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $user->moderatorOf()->where('entry_group_id', $entryGroup->entry_group_id->getValue())->count()
        );
    }

}
