<?php

declare(strict_types=1);

namespace App\Appli\PluginAdaptor;

use App\Dom\Plugin\PasswordPlugin;

class PasswordPluginAdaptor implements PasswordPlugin
{
    public function randomPassword(int $length = 8, array $characters = ['lower_case', 'upper_case', 'numbers', 'special_symbols'])
    {
        // define variables used within the function
        $symbols = array();
        $used_symbols = '';

        // an array of different character types
        $symbols["lower_case"] = 'abcdefghijklmnopqrstuvwxyz';
        $symbols["upper_case"] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols["numbers"] = '1234567890';
        $symbols["special_symbols"] = '!?~@#-_+<>[]{}';

        foreach ($characters as $value) {
            $used_symbols .= $symbols[$value]; // build a string with all characters
        }
        $symbols_length = strlen($used_symbols) - 1; //strlen starts from 0 so to get number of characters deduct 1

        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $symbols_length); // get a random character from the string with all characters
            $pass .= $used_symbols[$n]; // add the character to the password string
        }

        return $pass;
    }
}
