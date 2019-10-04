<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

$metadata['https://localhost/auth-sp'] = array (
  'entityid' => 'https://localhost/auth-sp',
  'contacts' => 
  array (
  ),
  'metadata-set' => 'saml20-sp-remote',
  'AssertionConsumerService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://localhost/auth-sp/acs',
      'index' => 1,
      'isDefault' => true,
    ),
  ),
  'SingleLogoutService' => 
  array (
  ),
  'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
  'keys' => 
  array (
    0 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIIEozCCAwugAwIBAgIUA0kZys4tLJLabVFZebafC5PJYmowDQYJKoZIhvcNAQEL
BQAwYTELMAkGA1UEBhMCRlIxDzANBgNVBAgMBkZyYW5jZTEQMA4GA1UEBwwHUmV6
w4PCqTEYMBYGA1UECgwPTGUgR3VlbiBCZWNxdWV5MRUwEwYDVQQDDAx0a2RvLWF1
dGgtc3AwHhcNMTkwOTI5MTk0MTAwWhcNMjkwOTI4MTk0MTAwWjBhMQswCQYDVQQG
EwJGUjEPMA0GA1UECAwGRnJhbmNlMRAwDgYDVQQHDAdSZXrDg8KpMRgwFgYDVQQK
DA9MZSBHdWVuIEJlY3F1ZXkxFTATBgNVBAMMDHRrZG8tYXV0aC1zcDCCAaIwDQYJ
KoZIhvcNAQEBBQADggGPADCCAYoCggGBAKD9A/U6XKSdikb3jFvRD5JMqzcIQ3dR
58YW1+aMCVH1nD0kLroPWchWq5smY15/pd5+tdXoAbjBMvlWD88rFuxWRmw5ckh2
GmGFxWgqle77IfnayWxkUryZuRsVy205pmcQkYexB3EbTtcDEiIeGCXSEn0Wf4ho
CehZ23xpeMWZXklu1DqIX3nv3uuE62BMEbvgs9PJAErmdAuU4STAvQ7FYvyXGQJz
k5feCDdtJ7/fSl8Jep8Z0Xk3jTtspMXklHjZA3WpNw14w5/xPY8eba8JTjfRXAAK
NC9py9dx286scB2dCxMbtfAEpmnlCGZ/QYPrSeD089fHINUMzdXlryRiGgk00pzF
d4vTLYiheytmFjijNdKhNarur/7nj4XekdXBf2oMGgyeJxqnzOnGWzPWErIlLXCH
fgI7GlHI8PQ8k7t68yH/iiSBqhnpNtORKpLl/XspmXxk2EZjcdembEn6TuNeF/VC
BCTyJF7ygZUtpAf0YnsuFw8+2sfgz4bVwwIDAQABo1MwUTAdBgNVHQ4EFgQUr4dr
ZttFCGIi0AWde+i3eHmqvm8wHwYDVR0jBBgwFoAUr4drZttFCGIi0AWde+i3eHmq
vm8wDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAYEARr5k0wTs307o
2XWz/O8bY7Wi67tAbAFs3D9XeQQxlYYPZTuGogxKZE9aaqdZinVsI9kt+JRMD77s
pTEAjdz7QIZIwBsGiZHKiQN9LYwLusN6b5YVyM/QJ2lJxU0faiUj9Eak3B+5mPwy
vORYk0rUw/78lINX3qiYZhyKjMzKNO5TU+2VptaX1eW0g+EyFqi4UZH9gsT4lf0B
uSWk5ZUQswqqgBHrv+PLDIrFHocMlvJWGSsEzJxWs8dQm9wsMi9HlN2Vps8/U4Kc
r4oKORpRtgKtEW8F40Ia0VnL2HJMalibFJLrFbSfwblySbpn/RtY8dO0BgAEc4+D
LcWbQkmuW7KGMzlXvWaTkDwEB0angNZj1zOsGhNBUEk5c4b93SP2MK0jj9o+jRX0
7xnlCeAkoV5lG3nJrlS20Zxby63T7xuFD1C7geuPkisKV0NgWTLbm6eLPyEDT7N0
mBOWJKFrOtCfP0cfTMcjlRizUEE5bAuMjQbdBs4a45w1ycUxLKAB
',
    ),
  ),
);
