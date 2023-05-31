<?php

namespace Identity\Application\Services;

use Identity\Domain\User\Models\Attributes\UserId;
use Identity\Domain\User\Models\User;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RsaService
{
    private EventDispatcher $dispatcher;
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $master_password
     * @return array
     */
    public function generateKeyPair(string $master_password): array
    {

        $privateKey = RSA::createKey(4096);
        /**
         * @var $privateKey PrivateKey
         */
        $privateKey = $privateKey->withHash('sha512');
        /**
         * @var $privateKey PrivateKey
         * @var $publicKey PublicKey
         */
        $privateKey = $privateKey->withPassword($master_password);

        $publicKey = $privateKey->getPublicKey();

        return [
            $privateKey,
            $publicKey
        ];
    }

    public function storeUserPrivateKey(UserId $userId, PrivateKey $privateKey): void
    {
        Storage::disk('identity_keys')->put($userId->getValue(), (string)$privateKey);
    }

    public function getUserPrivateKeyString(UserId $userId): string
    {
        $filesystem = Storage::disk('identity_keys');
        if (!$filesystem->exists($userId->getValue())) {
            throw new RuntimeException('Private key for user ' . $userId->getValue() . ' does not exists');
        }
        return $filesystem->get($userId->getValue());
    }

    public function getUserPrivateKey(UserId $userId, string $master_password): \phpseclib3\Crypt\Common\PrivateKey
    {
        return PublicKeyLoader::loadPrivateKey($this->getUserPrivateKeyString($userId), $master_password);
    }

    public function getUserPublicKey(User $user): \phpseclib3\Crypt\Common\PublicKey
    {
        return PublicKeyLoader::loadPublicKey($user->public_key->getValue());
    }
}
