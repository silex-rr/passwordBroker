<?php

namespace PasswordBroker\Infrastructure;

use PasswordBroker\Infrastructure\Services\PasswordGenerator;
use Tests\TestCase;

class PasswordGeneratorTest extends TestCase
{

    public function test_password_generator_can_generate_a_password(): void
    {
        $passwordGenerator = new PasswordGenerator();
        $passwordGenerator->setSymbolsLetterBrackets(true);
        $passwordGenerator->setSymbolsLetterDigits(true);
        $passwordGenerator->setSymbolsLetterLowercase(true);
        $passwordGenerator->setSymbolsLetterBrackets(true);
        $passwordGenerator->setSymbolsLetterUppercase(true);
        $passwordGenerator->setLength(14);

        $this->assertNotSame('', $passwordGenerator->generate());
    }
    public function test_password_generator_can_generate_passwords(): void
    {
        $passwordGenerator = new PasswordGenerator();
        $passwordGenerator->setSymbolsLetterBrackets(true);
        $passwordGenerator->setSymbolsLetterDigits(true);
        $passwordGenerator->setSymbolsLetterLowercase(true);
        $passwordGenerator->setSymbolsLetterBrackets(true);
        $passwordGenerator->setSymbolsLetterUppercase(true);
        $passwordGenerator->setLength(14);

        $this->assertCount(20, $passwordGenerator->generateList(20));
    }
}
