<?php

namespace Identity\Application;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\Attributes\UserName;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Tests\TestCase;
use function Psy\debug;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_a_system_admin_can_create_a_user(): void
    {
        /**
         * @var User $system_admin
         */
        $system_admin = User::factory()->create(['is_admin' => new IsAdmin(true)]);
        $userAttributesDef = $userAttributes = User::factory()->make()->getAttributes();
        $userAttributes['username'] = $userAttributes['name'];
        $userAttributes['password_confirmation'] = $userAttributes['password'];
        $userAttributes['master_password'] = UserFactory::MASTER_PASSWORD;
        $userAttributes['master_password_confirmation'] = UserFactory::MASTER_PASSWORD;

        $this->actingAs($system_admin);
//        dd($userAttributes);
        $this->postJson(route('registration'), ['user' => $userAttributes])->assertStatus(200);
        unset(
            $userAttributesDef['user_id'],
            $userAttributesDef['email_verified_at'],
            $userAttributesDef['remember_token'],
            $userAttributesDef['password'],
            $userAttributesDef['public_key'],
        );

        $this->assertDatabaseHas($system_admin->getTable(), $userAttributesDef, $system_admin->getConnectionName());
    }

    public function test_a_system_admin_can_delete_a_user(): void
    {
        /**
         * @var User $system_admin
         * @var User $user
         */
        [$system_admin, $user] = User::factory()->count(2)->create();
        $system_admin->is_admin = new IsAdmin(true);
        $this->actingAs($system_admin);

        $system_admin->save();

        $users_num = User::count();

        $this->deleteJson(route('user', ['user' => $user]))->assertStatus(200);
        $this->assertEquals($users_num - 1,
            User::count()
        );
        $this->assertDatabaseMissing($system_admin->getTable(), $user->getAttributes(), $user->getConnectionName());
    }

    public function test_a_system_admin_can_update_a_user(): void
    {
        /**
         * @var User $system_admin
         * @var User $user
         */
        [$system_admin, $user] = User::factory()->count(2)->create();

        $attributes = $user->getAttributes();
        $attributes['username'] = $attributes['name'] . '_new';
        unset($attributes['password']);

        $this->actingAs($system_admin);
        $system_admin->is_admin = new IsAdmin(true);
        $system_admin->save();

        $this->putJson(route('user', ['user' => $user]), ['user' => $attributes])->assertStatus(200);

        /**
         * @var User $userDB
         */
        $userDB = User::where('user_id', $user->user_id->getValue())->firstOrFail();

        $this->assertEquals($userDB->name->getValue(), $attributes['name']);
    }


    public function test_a_guest_cannot_see_a_user(): void
    {
        $user = User::factory()->create();

        $this->getJson(route('user', $user))->assertStatus(401);
    }

    public function test_a_guest_cannot_add_a_user():void
    {
        $userAttributes = User::factory()->make()->getAttributes();
        $userAttributes['username'] = $userAttributes['name'];
        $userAttributes['password_confirmation'] = $userAttributes['password'];
        $userAttributes['master_password'] = UserFactory::MASTER_PASSWORD;
        $userAttributes['master_password_confirmation'] = UserFactory::MASTER_PASSWORD;

        $users_num = User::count();

        $this->postJson(route('registration'), ['user' => $userAttributes])->assertStatus(401);

        $this->assertEquals($users_num, User::count());
    }

    public function test_a_guest_cannot_delete_a_user():void
    {
        $user = User::factory()->create();

        $users_num = User::count();

        $this->deleteJson(route('user', $user))->assertStatus(401);

        $this->assertEquals($users_num, User::count());
    }

    public function test_a_guest_cannot_update_a_user():void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $attributes = $user->getAttributes();
        $name_original = $attributes;
        $attributes['name'] .= '_new';

        $this->putJson(route('user', $user), $attributes)->assertStatus(401);

        /**
         * @var User $userDB
         */
        $userDB = User::where('user_id', $user->user_id)->firstOrFail();

        $this->assertTrue($user->name->equals($userDB->name));
    }


    public function test_a_user_can_see_an_other_user(): void
    {
        /**
         * @var User $user_1
         * @var User $user_2
         */
        [$user_1, $user_2] = User::factory()->count(2)->create();

        $this->actingAs($user_1);

        $this->getJson(route('user', $user_2))->assertStatus(200)
            ->assertJson(fn (AssertableJson $userJson)
                 => $userJson->where('user_id', $user_2->user_id->getValue())->etc()
            );
    }

    public function test_a_user_cannot_add_a_user():void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();

        $userAttributes = User::factory()->make()->getAttributes();
        $userAttributes['username'] = $userAttributes['name'];
        $userAttributes['password_confirmation'] = $userAttributes['password'];
        $userAttributes['master_password'] = UserFactory::MASTER_PASSWORD;
        $userAttributes['master_password_confirmation'] = UserFactory::MASTER_PASSWORD;

        $users_num = User::count();

        $this->actingAs($user);

        $this->postJson(route('registration'), ['user' => $userAttributes])->assertStatus(403);

        $this->assertEquals($users_num, User::count());
    }

    public function test_a_user_cannot_delete_a_user():void
    {
        /**
         * @var User $user
         * @var User $user_target
         */
        [$user, $user_target] = User::factory()->count(2)->create();

        $users_num = User::count();

        $this->actingAs($user);

        $this->deleteJson(route('user', $user_target))->assertStatus(403);

        $this->assertEquals($users_num, User::count());
    }

    public function test_a_user_cannot_update_an_other_user():void
    {
        /**
         * @var User $user
         * @var User $user_target
         */
        [$user, $user_target] = User::factory()->count(2)->create();
        $attributes = $user_target->getAttributes();
        $name_original = $attributes;
        $attributes['name'] .= '_new';

        $this->actingAs($user);

        $this->putJson(route('user', $user_target), $attributes)->assertStatus(403);

        /**
         * @var User $userDB
         */
        $user_targetDB = User::where('user_id', $user_target->user_id)->firstOrFail();

        $this->assertTrue($user_target->name->equals($user_targetDB->name));
    }

    public function test_search(): void
    {
        /**
         * @var User $admin
        */
        $admin = User::factory()->create(['name' => new UserName('admin')]);
        /**
         * @var User $user_1
         */
        $user_1 = User::factory()->create(['name' => new UserName('user_john')]);
        /**
         * @var User $user_2
         */
        $user_2 = User::factory()->create(['name' => new UserName('user_alex')]);
        /**
         * @var User $user_3
         */
        $user_3 = User::factory()->create(['name' => new UserName('user_jeremy')]);
        /**
         * @var User $user_already_in_entry_group
         */
        $user_already_in_entry_group = User::factory()->create(['name' => new UserName('user_jonathan')]);

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();
        $entryGroup->addAdmin($admin, $this->faker->password(128,128));
        $entryGroup->addMember($user_already_in_entry_group, $this->faker->password(128,128));

        $this->actingAs($admin);

//        $user_ids = [
//            $user_1->user_id->getValue(),
//            $user_2->user_id->getValue(),
//            $user_3->user_id->getValue(),
//        ];
//        $this->assertDatabaseHas($user_1->getTable(), ['user_id' => $user_1->user_id->getValue()]);
//
//        $this->getJson(route('user_search', [
//            'q' => '',
//            'entryGroupExclude' => $entryGroup->entry_group_id->getValue()
//        ]))->assertStatus(200)
//            ->assertJson(fn (AssertableJson $response)
//                => $response->has('data', count($user_ids), fn(AssertableJson $user)
//                   => $user->where('user_id', fn ($user_id) => in_array($user_id, $user_ids, true))
//                        ->etc()
//                )->etc()
//            );

        $user_ids = [
            $user_1->user_id->getValue(),
            $user_3->user_id->getValue(),
        ];
        $this->getJson(route('user_search', [
            'q' => 'r_j',
            'entryGroupExclude' => $entryGroup->entry_group_id->getValue()
        ]))->assertStatus(200)
            ->assertJson(fn (AssertableJson $response)
                => $response->has('data', count($user_ids), fn(AssertableJson $user)
                    => $user->where('user_id', fn ($user_id) => in_array($user_id, $user_ids, true))
                        ->etc()
                )->etc()
            );
    }
}
