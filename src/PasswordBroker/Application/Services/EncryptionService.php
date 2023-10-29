<?php

namespace PasswordBroker\Application\Services;

use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\Common\BlockCipher;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EncryptionService
{
    private string $cbcSalt = '';
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

    public function getCbcSalt(): string
    {
        if (empty($this->cbcSalt)) {
            $this->loadCbcSalt();
        }
        return $this->cbcSalt;
    }

    private function loadCbcSalt(): void
    {
        $filesystem = Storage::disk('cbc_salt');
        $fileName = 'salt';
        if(!$filesystem->exists($fileName)) {
            $filesystem->put($fileName, Random::string('32'));
        }
        $this->cbcSalt = $filesystem->get($fileName);
    }

    private function cipherSetPass(BlockCipher $cipher, string $pass): void
    {
        $cipher->setPassword($pass, 'pbkdf2', 'sha1', $this->getCbcSalt(), 1000, 16);
    }

    /**
     * @param string $data
     * @param string $pass
     * @param string $iv Initialization Vector for Rijndael
     * @return string
     */
    public function encrypt(string $data, string $pass, string $iv): string
    {
        $cipher = new Rijndael('cbc');
        $cipher->setIV($iv);
        $this->cipherSetPass($cipher, $pass);
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
        $cipher = new Rijndael('cbc');
        $cipher->setIV($iv);
        $this->cipherSetPass($cipher, $decrypted_aes_password);
        return $cipher->decrypt($data_encrypted);
    }
}
