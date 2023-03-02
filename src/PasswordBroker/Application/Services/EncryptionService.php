<?php

namespace PasswordBroker\Application\Services;

use PasswordBroker\Domain\Entry\Models\Fields\Field;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EncryptionService
{
    private EventDispatcher $dispatcher;
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
     * @param string $pass
     * @param string $iv Initialization Vector for Rijndael
     * @return string
     */
    public function decrypt(string $data_encrypted, string $pass, string $iv): string
    {
        $cipher = new Rijndael('ctr');
        $cipher->setIV($iv);
        $cipher->setPassword($pass);
        return $cipher->decrypt($data_encrypted);
    }

    /**
     * @param Field $field
     * @param $pass
     * @return string
     */
    public function decryptField(Field $field, $pass): string
    {
        return $this->decrypt($field->value_encrypted->getValue(), $pass, $field->initialization_vector->getValue());
    }
}
