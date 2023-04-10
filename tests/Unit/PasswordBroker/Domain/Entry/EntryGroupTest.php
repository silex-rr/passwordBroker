<?php

namespace Tests\Unit\PasswordBroker\Domain\Entry;

use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class EntryGroupTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_an_entry_group_can_have_an_administrator(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $user
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);
//        $entryGroup->addAdmin($user);
        $entryGroupService->addUserToGroupAsAdmin($user, $entryGroup);
        $this->assertEquals(
            1,
            $entryGroup->admins()->where('user_id', $user->user_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->count()
        );
        $this->assertEquals(
            0,
            $entryGroup->members()->where('user_id', $user->user_id->getValue())->count()
        );

    }

    public function test_an_entry_group_can_have_an_moderator(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $user
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

//        $entryGroup->addModerator($user);
        $entryGroupService->addUserToGroupAsModerator($user, $entryGroup);

        $this->assertEquals(
            0,
            $entryGroup->admins()->where('user_id', $user->user_id->getValue())->count()
        );

        $this->assertEquals(
            1,
            $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->count()
        );
        $this->assertEquals(
            0,
            $entryGroup->members()->where('user_id', $user->user_id->getValue())->count()
        );

    }

    public function test_an_entry_group_can_have_an_member(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $user
         */
        $user = User::factory()->create();
        $entryGroup = EntryGroup::factory()->create();


        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);
//        $entryGroup->addMember($user);
        $entryGroupService->addUserToGroupAsMember($user, $entryGroup);
        $this->assertEquals(
            0,
            $entryGroup->admins()->where('user_id', $user->user_id->getValue())->count()
        );

        $this->assertEquals(
            0,
            $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->count()
        );
        $this->assertEquals(
            1,
            $entryGroup->members()->where('user_id', $user->user_id->getValue())->count()
        );
    }

    public function test_an_entry_group_can_have_an_entry(): void
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();
        $entry = Entry::factory()->create();
        $this->assertInstanceOf(HasMany::class, $entryGroup->entries());

        $entryGroup->entries()->save($entry);

        $this->assertEquals(
            1,
            $entryGroup->entries()->where('entry_id', $entry->entry_id)->count()
        );
    }

    public function test_an_entry_group_can_belong_to_another_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup_1
         * @var EntryGroup $entryGroup_2
         */
        [$entryGroup_1, $entryGroup_2] = EntryGroup::factory()->count(2)->create();

        $this->assertEquals(
            0,
            $entryGroup_1->entryGroups()->count()
        );
        $entryGroup_1->entryGroups()->save($entryGroup_2);
        $this->assertEquals(
            1,
            $entryGroup_1->entryGroups()->where('entry_group_id', $entryGroup_2->entry_group_id)->count()
        );
    }

    public function test_an_entry_group_can_have_a_parent_entry_group(): void
    {
        /**
         * @var EntryGroup $entryGroup_1
         * @var EntryGroup $entryGroup_2
         */
        [$entryGroup_1, $entryGroup_2] = EntryGroup::factory()->count(2)->create();
        $this->assertNull($entryGroup_1->parentEntryGroup()->first());
        $entryGroup_1->parentEntryGroup()->associate($entryGroup_2);
        $this->assertTrue($entryGroup_1->parentEntryGroup()->first()->is($entryGroup_2));
    }

    public function test_an_entry_group_have_a_materialized_path(): void
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();
        $this->assertEquals($entryGroup->entry_group_id->getValue(), $entryGroup->materialized_path->getValue());
    }

}
