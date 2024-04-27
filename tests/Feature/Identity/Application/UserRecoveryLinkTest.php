<?php

namespace Identity\Application;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserRecoveryLinkTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_an_admin_can_create_a_invite_link(): void
    {
        /**
         * @var User $system_admin
         */
        $system_admin = User::factory()->create(['is_admin' => new IsAdmin(true)]);

        $this->actingAs($system_admin);

        $userAttributes = [
            'email' => $this->faker->email
        ];

        $this->postJson(route('invite'), [
            'user' => $userAttributes,
        ])->assertStatus(200)
            ->assertJson(static function (AssertableJson $json) {
                $json->has('inviteLinkUrl');
            });

        /**
         * @var User $user
         */
        $user = User::where($userAttributes)->first();
        $this->assertNotNull($user);

        $where_arr = ['user_id' => $user->user_id->getValue()];
        $this->assertDatabaseHas('identity_recovery_links', $where_arr);

        /**
         * @var RecoveryLink $recoveryLink
         */
        $recoveryLink = RecoveryLink::where($where_arr)->firstOrFail();

        $userAttributes = User::factory()->make()->getAttributes();
        unset($userAttributes['email'], $userAttributes['public_key'], $userAttributes['remember_token'],
            $userAttributes['email_verified_at']);
        $userAttributes['password_confirmation'] = $userAttributes['password'];
        $userAttributes['master_password'] = UserFactory::MASTER_PASSWORD;
        $userAttributes['master_password_confirmation'] = UserFactory::MASTER_PASSWORD;

        $this->actingAsGuest();

        $this->patchJson(route('invite_landing', $recoveryLink), ['user' => $userAttributes])
            ->assertStatus(Response::HTTP_OK);

        $this->post(route('login'),
            [
                'email' => $user->email->getValue(),
                'password' => $userAttributes['password'],
            ]
        )->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json->where('message', "Login successful"));
    }

    public function test_a_user_can_recovery_password(): void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();


        $this->postJson(route('recovery'), [
            'user' => [
                'email' => $user->email->getValue(),
                ],
        ])->assertStatus(200);

        /**
         * @var RecoveryLink $recoveryLink
         */
        $recoveryLink = RecoveryLink::where(['user_id' => $user->user_id->getValue()])->firstOrFail();

        $new_password = User::factory()->make()->getAttributes()['password'];

        $this->patchJson(route('recovery_landing', $recoveryLink), [
            'user' => [
                'password' => $new_password,
                'password_confirmation' => $new_password,
            ],
            'fingerprint' => json_encode(['test' => $this->faker->text()])

        ])->assertStatus(Response::HTTP_OK);

        $this->post(route('login'), [
            'email' => $user->email->getValue(),
            'password' => $new_password,
        ])->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json->where('message', "Login successful"));

    }

}
