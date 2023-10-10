<?php

namespace Tests\Feature\PasswordBroker\Application;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use phpDocumentor\Reflection\Types\Static_;
use Symfony\Component\Mime\Encoder\Base64Encoder;
use Tests\TestCase;

class EntryGroupTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use EntryGroupRandomAttributes;
    use PasswordHelper;

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
        $this->actingAs($user);

        $attributes = $this->getEntryGroupRandomAttributes();
        unset($attributes['materialized_path']);
        $this->postJson(route('entryGroups'), $attributes)->assertStatus(200);

        $this->assertDatabaseHas(app(EntryGroup::class)->getTable(), $attributes, app(EntryGroup::class)->getConnection()->getName());
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

        $this->actingAs($member);

        $this->getJson(route('entryGroups'))->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $entries) => $entries->has(1)->first(
                    fn(AssertableJson $entry) => $entry->where('entry_group_id', $entryGroup->entry_group_id->getValue())->etc()
                )
            );
    }

    public function test_a_user_can_see_their_entry_groups_as_tree(): void
    {
        /**
         * @var User $member
         * @var EntryGroup $entryGroup_0 EntryGroup
         * @var EntryGroup $entryGroup_0_1 EntryGroup
         * @var EntryGroup $entryGroup_0_2 EntryGroup
         * @var EntryGroup $entryGroup_0_1_3 EntryGroup
         * @var EntryGroup $entryGroup_0_1_4 EntryGroup
         * @var EntryGroup $entryGroup_0_1_3_5 EntryGroup
         * @var EntryGroup $entryGroup_0_1_3_6 EntryGroup
         */
        [$member, $another_member] = User::factory()->count(2)->create();

        $entryGroup_0 = EntryGroup::factory()->create(['name' => new GroupName('0')]);
        $entryGroup_0_1 = EntryGroup::factory()->create(['name' => new GroupName('0_1')]);
        $entryGroup_0_2 = EntryGroup::factory()->create(['name' => new GroupName('0_2')]);
        $entryGroup_0_1_3 = EntryGroup::factory()->create(['name' => new GroupName('0_1_3')]);
        $entryGroup_0_1_4 = EntryGroup::factory()->create(['name' => new GroupName('0_1_4')]);
        $entryGroup_0_1_3_5 = EntryGroup::factory()->create(['name' => new GroupName('0_1_3_5')]);
        $entryGroup_0_1_3_6 = EntryGroup::factory()->create(['name' => new GroupName('0_1_3_6')]);


        $entryGroup_0->entryGroups()->save($entryGroup_0_1);
        $entryGroup_0->entryGroups()->save($entryGroup_0_2);
        $entryGroup_0_1->entryGroups()->save($entryGroup_0_1_3);
        $entryGroup_0_1->entryGroups()->save($entryGroup_0_1_4);
        $entryGroup_0_1_3->entryGroups()->save($entryGroup_0_1_3_5);
        $entryGroup_0_1_3->entryGroups()->save($entryGroup_0_1_3_6);

        $entryGroup_0_1->addMember($member, $this->faker->password(128, 128));
        $entryGroup_0_1_3_5->addMember($member, $this->faker->password(128, 128));
        $entryGroup_0_1_3_6->addMember($member, $this->faker->password(128, 128));

        $entryGroup_0->addMember($another_member, $this->faker->password(128, 128));
        $entryGroup_0_2->addMember($another_member, $this->faker->password(128, 128));
        $entryGroup_0_1_3->addMember($another_member, $this->faker->password(128, 128));
        $entryGroup_0_1_4->addMember($another_member, $this->faker->password(128, 128));

        /**
         * @var EntryGroup $entryGroup_01 EntryGroup
         * @var EntryGroup $entryGroup_01_1 EntryGroup
         * @var EntryGroup $entryGroup_01_2 EntryGroup
         */
        $entryGroup_01 = EntryGroup::factory()->create(['name' => new GroupName('01')]);
        $entryGroup_01_1 = EntryGroup::factory()->create(['name' => new GroupName('01_1')]);
        $entryGroup_01_2 = EntryGroup::factory()->create(['name' => new GroupName('01_2')]);

        $entryGroup_01->entryGroups()->save($entryGroup_01_1);
        $entryGroup_01->entryGroups()->save($entryGroup_01_2);

        $entryGroup_01_2->addMember($member, $this->faker->password(128, 128));


        $this->actingAs($member);

        $this->getJson(route('entryGroupsAsTree'))
            ->assertStatus(200)
            ->assertJson(
                function (AssertableJson $json) use ($entryGroup_01_2, $entryGroup_0_1_3_6, $entryGroup_0_1_3_5, $entryGroup_0_1_3, $entryGroup_0, $entryGroup_01, $entryGroup_0_1) {
                    //We have to use this way bcz we work with unordered tree structure
                    $trees = $json->toArray()['trees'];
//                    dd($trees);
                    $entryGroup_0_found = false;
                    $entryGroup_01_found = false;
                    foreach ($trees as $tree) {
                        if ($entryGroup_0->entry_group_id->getValue() === $tree['entry_group_id']) {
                            $entryGroup_0_found = true;
                            $this->assertCount(1, $tree['children']);
                            $group_0_1 = $tree['children'][0];
                            if ($entryGroup_0_1->entry_group_id->getValue() !== $group_0_1['entry_group_id']) {
                                $this->fail('Miss child 0_1');
                            }
                            $this->assertCount(1, $group_0_1['children']);
                            $group_0_1_3 = $group_0_1['children'][0];
                            if ($entryGroup_0_1_3->entry_group_id->getValue() !== $group_0_1_3['entry_group_id']) {
                                $this->fail('Miss child 0_1_3');
                            }
                            $this->assertCount(2, $group_0_1_3['children']);
                            $needed_children = [$entryGroup_0_1_3_5->entry_group_id->getValue() => null, $entryGroup_0_1_3_6->entry_group_id->getValue() => null];
                            foreach ($group_0_1_3['children'] as $child) {
                                if (array_key_exists($child['entry_group_id'], $needed_children)) {
                                    unset($needed_children[$child['entry_group_id']]);
                                }
                            }
                            $this->assertCount(0, $needed_children, 'Some child of 0_1_3 does not found ' . implode(', ', array_keys($needed_children)));

                            continue;
                        }
                        if ($entryGroup_01->entry_group_id->getValue() === $tree['entry_group_id']) {
                            $entryGroup_01_found = true;

                            $this->assertCount(1, $tree['children']);
                            $group_01_2 = $tree['children'][0];
                            if ($entryGroup_01_2->entry_group_id->getValue() !== $group_01_2['entry_group_id']) {
                                $this->fail('Miss child 01_1');
                            }

                            continue;
                        }
                        $this->fail('Unexpected tree found in response: ' . print_r($tree, true));
                    }

                    if (!$entryGroup_0_found) {
                        $this->fail('Tree ' . $entryGroup_0->entry_group_id->getValue() . ' ' . $entryGroup_0->name->getValue() . ' does not exists in response');
                    }
                    if (!$entryGroup_01_found) {
                        $this->fail('Tree ' . $entryGroup_01->entry_group_id->getValue() . ' ' . $entryGroup_01->name->getValue() . ' does not exists in response');
                    }
                    $json->etc();
                }

//                => $json->has('trees', 2, fn (AssertableJson $tree)
//                    => $tree->where('entry_group_id', $entryGroup_0->entry_group_id->getValue())
//                    ->has('children', 1, fn (AssertableJson $entryGroup_0_json_children) // 1 bcz member does  have access to only one group here
//                        => $entryGroup_0_json_children->where('entry_group_id', $entryGroup_0_1->entry_group_id->getValue())
//                        ->has('children', 1, fn(AssertableJson $entryGroup_0_1_json_children)
//                            => $entryGroup_0_1_json_children->where('entry_group_id', $entryGroup_0_1_3->entry_group_id->getValue())
//                            ->has('children', 2)->each(function (AssertableJson $entryGroup_0_1_3_json_children)
//                            {
//                                print_r($entryGroup_0_1_3_json_children->toArray());
//                            }
////                                => $entryGroup_0_1_3_json_children->each(fn(AssertableJson $entryGroup_0_1_3_json_child)
////                                    => $entryGroup_0_1_3_json_child->where('entry_group_id',
////                                        fn (string $entry_group_id) => in_array($entry_group_id,
////                                            [
////                                                $entryGroup_0_1_3_5->entry_group_id->getValue(),
////                                                $entryGroup_0_1_3_6->entry_group_id->getValue(),
////                                            ], true
////                                        ))
////                                )->etc()
//                            )->etc()
//                        )->etc()
//                    )->etc()
//                )

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

        $entryGroup_1->addAdmin($user_1, $this->faker()->password(128, 128));
        $entryGroup_2->addAdmin($user_2, $this->faker()->password(128, 128));

        $this->actingAs($user_1);

        $this->getJson(route('entryGroups'))->assertStatus(200)->assertJson(
            fn(AssertableJson $entries) => $entries->has(1)->first(
                fn(AssertableJson $entry) => $entry
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

        $this->actingAs($admin);

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

        $this->actingAs($moderator);

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

        $this->actingAs($member);

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

        $this->actingAs($admin);

        $this->assertDatabaseHas($entryGroup->getTableFullName(),
            $entryGroup->getAttributes()
        );

        $this->deleteJson(route('entryGroup', ['entryGroup' => $entryGroup->entry_group_id->getValue()]))
            ->assertStatus(403);

        $this->assertDatabaseHas($entryGroup->getTableFullName(),
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

        $this->actingAs($admin);

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

        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        $this->actingAs($moderator);

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

        $this->actingAs($admin);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroupTarget);

        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, master_password: UserFactory::MASTER_PASSWORD);
        $entryGroupService->addUserToGroupAsMember($member, $entryGroupTarget, master_password: UserFactory::MASTER_PASSWORD);

        $this->actingAs($member);

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

    public function test_only_a_system_administrator_can_get_all_groups_with_all_fields(): void
    {
//        $this->withoutExceptionHandling();
        /**
         * @var EntryGroup $entryGroup_1
         * @var EntryGroup $entryGroup_2
         * @var EntryGroup $entryGroup_3
         * @var EntryGroup $entryGroup_4
         * @var EntryGroup $entryGroup_5
         * @var User $admin
         * @var User $member
         * @var EntryGroupService $entryGroupService
         */
        [$entryGroup_1, $entryGroup_2, $entryGroup_3, $entryGroup_4, $entryGroup_5]
            = EntryGroup::factory()
            ->sequence(fn (Sequence $sequence) => ['name' => GroupName::fromNative('group_' . $sequence->index)])
        ->count(5)->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $admin->is_admin = IsAdmin::fromNative(true);
        $admin->save();
        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        $this->actingAs($admin);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup_1);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup_2);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup_3);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup_4);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup_5);

        /**
         * @var Entry $entry_3_1
         */
        $entry_3_1 = Entry::factory()->withEntryGroup($entryGroup_3)->create();


        $password_3_1_1 = $this->getPasswordHelper(
            owner: $admin,
            entryGroup: $entryGroup_3,
            entry: $entry_3_1,
            password_str: $this->faker->password
        );

        $this->getJson(route('allGroupsWithFields'))
            ->assertStatus(200)
            ->assertJson(static fn(AssertableJson $json) =>
                $json->has(5)
                    ->each(static function (AssertableJson $group)
                    use ($password_3_1_1, $entry_3_1, $entryGroup_3)
                {
                    if ($group->toArray()['entry_group_id'] === $entryGroup_3->entry_group_id->getValue()) {
                        $group->has('entries', 1, static function (AssertableJson $entry)
                            use ($password_3_1_1, $entry_3_1)
                        {
                            $entry->where('entry_id', $entry_3_1->entry_id->getValue());
                            $entry->has('passwords', 1, static function (AssertableJson $password)
                                use ($password_3_1_1)
                            {
                                /**
                                 * @var Base64Encoder $base64Encoder
                                 */
                                $base64Encoder = app(Base64Encoder::class);
                                $password->where('field_id', $password_3_1_1->field_id->getValue());
                                $password->where('encrypted_value_base64', $base64Encoder->encodeString(
                                    $password_3_1_1->value_encrypted->getValue())
                                );
                                $password->where('initialization_vector_base64', $base64Encoder->encodeString(
                                    $password_3_1_1->initialization_vector->getValue())
                                );
                                $password->etc();
                            });
                            $entry->etc();
                        });
                    }
                    $group->etc();
                })
            );

        $this->actingAs($member);
        $this->getJson(route('allGroupsWithFields'))
            ->assertStatus(403);

    }
}
