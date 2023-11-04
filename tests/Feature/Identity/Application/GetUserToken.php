<?php

namespace Tests\Feature\Identity\Application;

use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Illuminate\Testing\Fluent\AssertableJson;
use Ramsey\Uuid\Uuid;

trait GetUserToken
{
    /**
     * @param User $user
     * @param string|null $name
     * @return array
     */
    public function getUserToken(User $user, ?string $name = null): array
    {
        $this->actingAs($user);
        $token = '';
        if (is_null($name)) {
            $name = Uuid::uuid4()->toString();
        }

        $this->postJson(
            route('user_get_token', ['token_name' => $name])
        )->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use (&$token) {
                $json->has('token')->has('user');
                $token = $json->toArray()['token'];
            });
        $this->actingAsGuest();
        /**
         * @var UserAccessToken $userAccessToken
         */
        $userAccessToken = UserAccessToken::where('name', $name)->firstOrFail();
        return [
            $token,
            $userAccessToken
        ];
    }
}
