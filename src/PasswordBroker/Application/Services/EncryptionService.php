<?php

namespace PasswordBroker\Application\Services;

use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EncryptionService
{
    public function __construct(
        private readonly EventDispatcher $dispatcher
    )
    {
    }

    public function generateInitializationVector(): string
    {
        return Random::string(16);
    }

    public function generatePassword(): string
    {
        return Random::string(64);
    }

    /**
     * @param string $data
     * @param string $pass
     * @param string $iv Initialization Vector for Rijndael
     * @return string
     */
    public function encrypt(string $data, string $pass, string $iv): string
    {
        $cipher = new Rijndael('ctr');
        $cipher->setIV($iv);
        $cipher->setPassword($pass);
        return $cipher->encrypt($data);
    }

    /**
     * @param string $data_encrypted
     * @param string $decrypted_aes_password
     * @param string $iv Initialization Vector for Rijndael
     * @return string
     */
    public function decrypt(string $data_encrypted, string $decrypted_aes_password, string $iv): string
    {
        $cipher = new Rijndael('ctr');
        $cipher->setIV($iv);
        $cipher->setPassword($decrypted_aes_password);
        return $cipher->decrypt($data_encrypted);
    }
}
