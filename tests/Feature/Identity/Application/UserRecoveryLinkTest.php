<?php

namespace Tests\Feature\Identity\Application;

use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\Attributes\RecoveryLinkKey;
use Identity\Domain\User\Models\Attributes\RecoveryLinkStatus;
use Identity\Domain\User\Models\RecoveryLink;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Infrastructure\Services\PasswordGenerator;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserRecoveryLinkTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_only_an_admin_can_create_a_invite_link(): void
    {
        $userAttributes = [
            'email' => $this->faker->email
        ];

        $this->actingAsGuest();

        $this->postJson(route('invite'), [
            'user' => $userAttributes,
        ])->assertStatus(403);

        $system_admin = User::factory()->create(['is_admin' => new IsAdmin(true)]);
        $user = User::factory()->create(['is_admin' => new IsAdmin(false)]);

        $this->actingAs($system_admin);
        $this->postJson(route('invite'), [
            'user' => $userAttributes,
        ])->assertStatus(200)
            ->assertJson(static function (AssertableJson $json) {
                $json->has('inviteLinkUrl');
            });

        $this->actingAs($user);

        $this->postJson(route('invite'), [
            'user' => $userAttributes,
        ])->assertStatus(403);
    }

    public function test_invite_landing_validate_key(): void
    {
        $this->actingAsGuest();

        $email = $this->faker->email;
        $username = $this->faker->userName;

        $this->artisan('identity:addInviteLink',
            [
                'username' => $username,
                'email' => $email,
                '--force' => 'yes'
            ]
        )->expectsQuestion("Will " . $username . " be a super administrator?", 'yes')
            ->assertSuccessful();

        $recoveryLink = new RecoveryLink();

        $recoveryLink->key = new RecoveryLinkKey($this->faker->word());

        /**
         * @var PasswordGenerator $passwordGenerator
         */
        $passwordGenerator = app(PasswordGenerator::class);

        $password = $passwordGenerator->generate();
        $masterPassword = $passwordGenerator->generate();
        $this->patchJson(route('invite_landing', $recoveryLink), [
            'user' => [
                'password' => $password,
                'password_confirmation' => $password,
                'master_password' => $masterPassword,
                'master_password_confirmation' => $masterPassword,

            ],
        ])->assertStatus(Response::HTTP_BAD_REQUEST);

        /**
         * @var RecoveryLink $realRecoveryLink
         */
        $realRecoveryLink = RecoveryLink::whereHas('user',
            static function (Builder $q) use ($email){ $q->where('email', $email); }
        )->firstOrFail();

        $this->assertEquals(RecoveryLinkStatus::AWAIT, $realRecoveryLink->status);
    }

    public function test_an_admin_can_create_an_invite_link(): void
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

        $recoveryLink->refresh();
        $this->assertEquals(RecoveryLinkStatus::ACTIVATED, $recoveryLink->status);
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
