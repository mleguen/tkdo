<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

$metadata['https://localhost/auth-sp'] = [
    'AssertionConsumerService' => 'https://localhost/auth-sp/acs',
    'SingleLogoutService' => 'https://localhost/auth-sp/logout',
];
