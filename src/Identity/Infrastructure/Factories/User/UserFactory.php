<?php

namespace Identity\Infrastructure\Factories\User;

use App\Common\Domain\Abstractions\FactoryDomain;
use Identity\Application\Services\RsaService;
use Identity\Domain\User\Models\Attributes\Email;
use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\Attributes\PublicKey;
use Identity\Domain\User\Models\Attributes\UserId;
use Identity\Domain\User\Models\Attributes\UserName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method \Identity\Domain\User\Models\User | \Identity\Domain\User\Models\User[] create($attributes = [], ?Model $parent = null)
 */
class UserFactory extends FactoryDomain
{
    public const MASTER_PASSWORD = 'master_password_1';
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = UserId::fromNative(fake()->uuid());
        /**
         * @var $rsaService RsaService
         */
        $rsaService = app(RsaService::class);
        [$privateKey, $publicKey] = $rsaService->generateKeyPair(self::MASTER_PASSWORD);
        $rsaService->storeUserPrivateKey($userId, $privateKey);
        return [
            'user_id' => $userId,
//            'is_admin' => IsAdmin::fromNative(false),
            'name' => UserName::fromNative(fake()->name()),
            'email' => Email::fromNative(fake()->safeEmail()),
            'email_verified_at' => now(),
            'password' => fake()->password(12) . fake()->numberBetween(),
            'remember_token' => Str::random(10),
            'public_key' => PublicKey::fromNative((string)$publicKey)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            $attributes['email_verified_at'] = null;
            return $attributes;
        });
    }

    public function systemAdministrator(bool $isSystemAdministrator = true): static
    {
        return $this->state(function (array $attributes) use ($isSystemAdministrator) {
            $attributes['is_admin'] = IsAdmin::fromNative($isSystemAdministrator);
            return $attributes;
        });
    }
}
