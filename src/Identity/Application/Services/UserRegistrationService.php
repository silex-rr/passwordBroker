<?php

namespace Identity\Application\Services;

use Identity\Domain\User\Models\Attributes\Email;
use Identity\Domain\User\Models\Attributes\IsAdmin;
use Identity\Domain\User\Models\Attributes\PublicKey;
use Identity\Domain\User\Models\Attributes\UserName;
use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UserRegistrationService
{
    private EventDispatcher $dispatcher;
    private RsaService $rsaService;
    public function __construct(EventDispatcher $dispatcher, RsaService $rsaService)
    {
        $this->dispatcher = $dispatcher;
        $this->rsaService = $rsaService;
    }

    public function execute(
        string $email,
        string $username,
        string $password,
        string $master_password,
        bool $isAdmin = false
    ): User
    {
        if (!$this->validateUser($email, $username, $password)) {
            throw new RuntimeException('Invalid User data');
        }
        $emailAttribute = new Email($email);
        $usernameAttribute = new UserName($username);
        $isAdminAttribute = new IsAdmin($isAdmin);
        $user = new User(['email' => $emailAttribute, 'name' => $usernameAttribute, 'is_admin' => $isAdminAttribute]);
        $user->password = Hash::make($password);
        $user->user_id;
        $user->is_admin = $isAdminAttribute;

        [$privateKey, $publicKey] = $this->rsaService->generateKeyPair($master_password);
        $user->public_key = new PublicKey((string)$publicKey);
        $this->rsaService->storeUserPrivateKey($user->user_id, $privateKey);

//        dd($user->is_admin);
        $user->save();
//        $this->dispatcher->dispatch();
        return $user;
    }

    public function validateUser($email, $username, $password): bool
    {
        return true;
    }
}
