<?php

namespace Identity\Application;

use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Ramsey\Uuid\Uuid;
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

    public function test_a_user_can_get_or_create_a_user_application(): void
    {
//        $this->withoutExceptionHandling();
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);
        $clientId = new ClientId(Uuid::uuid4());

        $application_id = null;

        $this->postJson(route('userApplications'), ['clientId' => $clientId->getValue()])
            ->assertStatus(200)
            ->assertJson(static function (AssertableJson $json) use (&$application_id, $clientId) {
                     $json
                         ->where('userApplication.client_id', $clientId->getValue())
                         ->where('userApplication.user_application_id',
                            static function ($json_application_id) use (&$application_id) {
                                $application_id = $json_application_id;
                                return Uuid::isValid($application_id);
                            });
                    }
            );

        $this->assertEquals(
            1,
            $user->applications()->where('client_id', $clientId->getValue())->count()
        );

        $this->postJson(route('userApplications'), ['clientId' => $clientId->getValue()])
            ->assertStatus(200)
            ->assertJson(static fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.user_application_id', $application_id)
            );
    }

    public function test_a_user_can_get_a_user_application(): void
    {
        $this->withoutExceptionHandling();
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);
        $clientId = new ClientId(Uuid::uuid4());
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->clientId($clientId)->belongToUser($user)->create();

        $this->getJson(route('userApplication', ['userApplication' => $userApplication->client_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.user_application_id', $userApplication->user_application_id->getValue())
                    ->etc()
            );

        $this->getJson(route('userApplication', ['userApplication' => $userApplication->user_application_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.client_id', $userApplication->client_id->getValue())
                    ->etc()
            );
    }
}
