<?php

declare(strict_types=1);

namespace App\Dom\Plugin;

interface PasswordPlugin
{
    /**
     * Return a pseudo-random password
     *
     * Adapted from https://www.phpjabbers.com/generate-a-random-password-with-php-php70.html
     * @param int $length the length of the generated password
     * @param string[] $characters types of characters to be used in the password
     * @return string the generated password
     */
    public function randomPassword(int $length = 8, array $characters = ['lower_case', 'upper_case', 'numbers', 'special_symbols']);
}
