<?php

namespace PasswordBroker\Infrastructure\Services;

use Random\Randomizer;
use function Symfony\Component\String\b;

class PasswordGenerator
{
    private const string SYMBOLS_LETTERS_LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const string SYMBOLS_LETTERS_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const string SYMBOLS_DIGITS = '0123456789';
    private const string SYMBOLS_COMMON = '-_';
    private const string SYMBOLS_BRACKETS = '()[]{}<>';
    private const string SYMBOLS_SPECIAL = '!@#$%^*&';

    private int $length = 12;
    private bool $try_to_put_all_selected_symbols_in_password = true;
    private bool $symbols_letter_lowercase = true;
    private bool $symbols_letter_uppercase = true;
    private bool $symbols_letter_digits = true;
    private bool $symbols_letter_common = true;
    private bool $symbols_letter_brackets = false;
    private bool $symbols_letter_special = false;

    private Randomizer $randomizer;

    public function __construct()
    {
        $this->randomizer = new Randomizer();
    }


    public function generate(): string
    {
        $passwordCandidate = $this->randomizer->getBytesFromString($this->getStringForRandomizer(), $this->length);

        return $this->finishPassword($passwordCandidate);
    }

    public function generateList(int $num): array
    {
        $out = [];
        while ($num-- > 0) {
            $out[] = $this->generate();
        }
        return $out;
    }
    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function isSymbolsLetterLowercase(): bool
    {
        return $this->symbols_letter_lowercase;
    }

    public function setSymbolsLetterLowercase(bool $symbols_letter_lowercase): void
    {
        $this->symbols_letter_lowercase = $symbols_letter_lowercase;
    }

    public function isSymbolsLetterUppercase(): bool
    {
        return $this->symbols_letter_uppercase;
    }

    public function setSymbolsLetterUppercase(bool $symbols_letter_uppercase): void
    {
        $this->symbols_letter_uppercase = $symbols_letter_uppercase;
    }

    public function isSymbolsLetterDigits(): bool
    {
        return $this->symbols_letter_digits;
    }

    public function setSymbolsLetterDigits(bool $symbols_letter_digits): void
    {
        $this->symbols_letter_digits = $symbols_letter_digits;
    }

    public function isSymbolsLetterCommon(): bool
    {
        return $this->symbols_letter_common;
    }

    public function setSymbolsLetterCommon(bool $symbols_letter_common): void
    {
        $this->symbols_letter_common = $symbols_letter_common;
    }

    public function isSymbolsLetterBrackets(): bool
    {
        return $this->symbols_letter_brackets;
    }

    public function setSymbolsLetterBrackets(bool $symbols_letter_brackets): void
    {
        $this->symbols_letter_brackets = $symbols_letter_brackets;
    }

    public function isSymbolsLetterSpecial(): bool
    {
        return $this->symbols_letter_special;
    }

    public function setSymbolsLetterSpecial(bool $symbols_letter_special): void
    {
        $this->symbols_letter_special = $symbols_letter_special;
    }

    private function getStringForRandomizer(): string
    {
        $string = '';
        if ($this->symbols_letter_lowercase) {
            $string .= self::SYMBOLS_LETTERS_LOWERCASE;
        }
        if ($this->symbols_letter_uppercase) {
            $string .= self::SYMBOLS_LETTERS_UPPERCASE;
        }
        if ($this->symbols_letter_digits) {
            $string .= self::SYMBOLS_DIGITS;
        }
        if ($this->symbols_letter_common) {
            $string .= self::SYMBOLS_COMMON;
        }
        if ($this->symbols_letter_brackets) {
            $string .= self::SYMBOLS_BRACKETS;
        }
        if ($this->symbols_letter_special) {
            $string .= self::SYMBOLS_SPECIAL;
        }
        return $string;
    }

    private function finishPassword(string $passwordCandidate): string
    {
        if (!$this->try_to_put_all_selected_symbols_in_password) {
            return $passwordCandidate;
        }
        $passwordCandidateArr = str_split($passwordCandidate);

        $symbolsPositions = $this->getSymbolsPositions($passwordCandidateArr);

        $this->improveSymbolsPositions($passwordCandidateArr, $symbolsPositions);

        return implode('', $passwordCandidateArr);
    }

    private function improveSymbolsPositions(array &$passwordCandidateArr, array $symbolsPositions): void
    {
        $symbolRangesCount = array_map(static fn ($v) => count($v), $symbolsPositions);

        $symbolsDonors = array_filter($symbolRangesCount, static fn ($value) => $value > 1);
        $symbolsToFill = array_keys(array_filter($symbolRangesCount, static fn ($value) => $value === 0));

        $symbolsDonorCount = count($symbolsDonors);

        if ($symbolsDonorCount === 0 || count($symbolsToFill) === 0) {
            return;
        }
        foreach ($symbolsToFill as $symbolToFill) {
            $max = max($symbolsDonors);
            if ($max <= 1) {
                break;
            }
            $newSymbol = $this->randomizer->getBytesFromString($symbolToFill, 1);

            $symbolsDonor = array_search($max, $symbolsDonors, true);
            $donorPosition = $this->randomizer->pickArrayKeys($symbolsPositions[$symbolsDonor], 1)[0];
            $position = $symbolsPositions[$symbolsDonor][$donorPosition];
            unset($symbolsPositions[$symbolsDonor][$donorPosition]);

            $passwordCandidateArr[$position] = $newSymbol;
            $symbolsPositions[$symbolToFill][] = $position;
        }
    }
    private function getSymbolsPositions(array $passwordCandidateArr): array
    {
        $positions = [];
        if ($this->symbols_letter_uppercase) {
            $positions[self::SYMBOLS_LETTERS_LOWERCASE] = [];
        }
        if ($this->symbols_letter_uppercase) {
            $positions[self::SYMBOLS_LETTERS_UPPERCASE] = [];
        }
        if ($this->symbols_letter_digits) {
            $positions[self::SYMBOLS_DIGITS] = [];
        }
        if ($this->symbols_letter_common) {
            $positions[self::SYMBOLS_COMMON] = [];
        }
        if ($this->symbols_letter_brackets) {
            $positions[self::SYMBOLS_BRACKETS] = [];
        }
        if ($this->symbols_letter_special) {
            $positions[self::SYMBOLS_SPECIAL] = [];
        }

        foreach ($passwordCandidateArr as $pos => $char) {
            foreach ($positions as $symbols => $n) {
                if (str_contains($symbols, $char)) {
                    $positions[$symbols][] = $pos;
                    break;
                }
            }
        }

        return $positions;
    }


}
