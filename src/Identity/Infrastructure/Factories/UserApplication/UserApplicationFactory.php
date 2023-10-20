<?php

namespace Identity\Infrastructure\Factories\UserApplication;

use App\Common\Domain\Abstractions\FactoryDomain;
use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Ramsey\Uuid\Uuid;

class UserApplicationFactory extends FactoryDomain
{

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'client_id' => new ClientId(Uuid::uuid4())
        ];
    }

    public function belongToUser(User $user): static
    {
        return $this->state(function ($attributes) use ($user) {
            $attributes['user_id'] = $user->user_id;
            return $attributes;
        });
    }

    public function randomApplicationId(): static
    {
        return $this->state(function ($attributes) {
            $attributes['user_application_id'] = new UserApplicationId();
            return $attributes;
        });
    }

    public function clientId(ClientId $clientId): static
    {
        return $this->state(function ($attributes) use ($clientId){
           $attributes['client_id'] = $clientId;
            return $attributes;
        });
    }

    public function createUser(): static
    {
        return $this->state(function ($attributes) {
            /**
             * @var User $sysAdmin
             */
            $sysAdmin = User::factory()->systemAdministrator()->create();
            $attributes['user_id'] = $sysAdmin->user_id;
            return $attributes;
        });
    }
}
