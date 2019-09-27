<?php

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    'example-userpass' => [
        'exampleauth:UserPass',

        // Give the user an option to save their username for future login attempts
        // And when enabled, what should the default be, to save the username or not
        //'remember.username.enabled' => false,
        //'remember.username.checked' => false,

        'alice:alice' => [
            'nom' => 'Alice',
            'roles' => ['participant'],
        ],
        'owen:owen' => [
            'nom' => 'Owen',
            'roles' => [],
        ],
    ],
    
];
