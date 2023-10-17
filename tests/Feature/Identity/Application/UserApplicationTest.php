<?php

namespace Identity\Application;

use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserApplicationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;


    public function test_a_system_administrator_can_switch_a_database_offline_mode_for_self_tokens(): void
    {

        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();

        /**
         * @var User $user
         */
        $user = $userApplication->user()->first();
        $this->actingAs($user);

        $this->getJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', false));

        $this->putJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]),
                ['status' => true]
            )->assertStatus(200);

        $this->getJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', true));
    }

    public function test_a_user_can_create_a_user_application(): void
    {
        $this->withoutExceptionHandling();
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);
        $userApplicationId = new UserApplicationId();

        $this->postJson(route('userApplication'), ['userApplicationId' => $userApplicationId->getValue()])
            ->assertStatus(200)
            ->assertJson(static fn (AssertableJson $json)
                => $json->where('userApplication.user_application_id', $userApplicationId->getValue())
            );
        $this->assertEquals(
            1,
            $user->applications()->where('user_application_id', $userApplicationId->getValue())->count()
        );
    }
}
