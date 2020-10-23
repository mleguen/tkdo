<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Application\Actions\ActionTestCase;

class AuthMiddlewareTest extends ActionTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->app->get('/test-auth-middleware', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write(json_encode($request->getAttribute('idUtilisateurAuth')));
            return $response;
        });
    }

    public function testAction()
    {
        $response = $this->handleRequestWithAuthHeader(
            'Bearer ' . $this->authService->encodeBearerToken(1),
            'GET',
            '/test-auth-middleware'
        );

        $this->assertEqualsResponse(200, '1', $response);
    }

    public function testActionPasUnBearerToken()
    {
        $response = $this->handleRequestWithAuthHeader(
            'pas un bearer token',
            'GET',
            '/test-auth-middleware'
        );

        $this->assertEqualsResponse(200, 'null', $response);
    }

    public function testActionPasUnJWT()
    {
        $response = $this->handleRequestWithAuthHeader(
            'Bearer pas un JWT',
            'GET',
            '/test-auth-middleware'
        );

        $this->assertEqualsResponse(200, 'null', $response);
    }

    public function testActionUnJWTExpire()
    {
        $response = $this->handleRequestWithAuthHeader(
            'Bearer ' . $this->authService->encodeBearerToken(1, ["validite" => -10]),
            'GET',
            '/test-auth-middleware'
        );

        $this->assertEqualsResponse(200, 'null', $response);
    }

    public function testActionUnJWTCreeAvecUneMauvaiseCle()
    {
        $mauvaiseClePrivee = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA115TjZW5Ul9k6dMvsgsjKwKW6TCRDjk3tr4ZMD7yDNycbzmG
jwsO34Dz79WOZMfZwDBoXcJEC2Py0Oau1GuBfLnq3pPSf9OCn4vneRuV7xKVdMZo
uBnvDltShUfmTWkRjgnhbYTl4se1fWc/WGhR3VhSaib9N0Pah4uxK76imFBedDfA
iwUJQblUr8WluKQBrF4Wls/mfh1wwAt8SWa9S4w6+UVMmS0zqYRVtZ3kKJM7ZS8B
zUKAUKG4l7w8tzaxcWE+587j5g3nPVftZ6GYbcBRXAbkcq7KF/Pao/OqPG3/9XV3
Vr5QtOsRJuWkzRy0yJg4ShHpZHtrZQqdEzPEtwIDAQABAoIBACNYs9XU8Ol4BpPp
uTY7ZbY1Ypc7BVOUSHSRloi4i/lYa8RFaLQpWHOOMnr2Tbx0oGROjZJ1w70q1js6
4Q6z5jiWOtn04ONhz1poF8FkqaLnJehYd+9fMkDk9BIzzrR9vgfVz02uNhyWMk82
lsrntCfjywLpCz36wO7mGlnXm8/rc5lzhDJFq3GwsFBrH1T0N2XX/1QaLEWKgaxq
9lmLDlEZkYmLEM1iEbKVrQYRYj2VabEjXbQMUhgA+3zxXH6+uRBh8a/6M29CdwvL
8vTd/aA6M67iM48RJrVM8Iq2cxgb1+JLXGEXYIe1CDIJHK49x9AOkNDpRm6f05xk
ILTqe0ECgYEA+lgS1DP/Ga0+jniMmqV7IJ3GZIiwDHOdcJt4bXTFfMZpjuW22eZX
wkxgBW1s4xTLP1FDm45XgZv3VOTIeh7Nb6zNlh0sQqJa8bFodJlRSkFf1Ug+zt2h
qeK+ofeqxh414fJEFMCFtgvqFCLPZXz/GLVABzOe9+Gk5KkvV0YYeI8CgYEA3Dv2
hTFfq8+Y5+Qf0gvvXktxA+1WxldDL0s2K2Edlw464tbNgvexMUbhHB31lyQqD4ic
4n/MSeh6Uv4kX94ePeLHlzqARpjD2bGQfULRuL9fuaXHEvtINFUb8uXPp7LbGrmG
JaV1lyjS6Z3nP2xtb5J6igT6JZbfO1P/I1Af9VkCgYEA7cHr0AG6C4bP3Lt8vfrf
33A41Y5DtO+w3Ruq2jmGviQqaH6elIABZRToNP9h5KEBDxd84CXl0cBwu/20sNbE
QYWakzENshND0Duvet2JqL5B5+v/SrSPLyub5XO0iwhyIC7YneTimKzkGU+eLULz
e2HYd359QerZkhlkTrzRzN0CgYEAse+oj5iCqbgC3GiN5RFLhq4BU6DwmTNrzsw/
qfC/DYqrvRa72HLzMNMJkcQK5uCLMx23U7DZh2VTX7aCAQre+DU/+UmE1oUax8oU
6X+RHmlQmBf+rbFkdxPEjw1qJx30tLTsU4JJbaXIMoQnkDC0go6gft7ilHg/SBnn
a8qS55ECgYEAsRtsecQwzwiQUCSp8pJkc1yaNZCp7dSPsaFV3jxUa+GEcbKD7pxJ
OntNhO592PxY1J9kraiYxIEq+SyZqlw1MrVqE0KXEtS+7JWTqBcE9piZ0G1J2jB/
LonNH8upjaTgg3DS6VuHWg/rJyr9qPzTNAfqEkv1pz6t7jqoIHRm5J4=
-----END RSA PRIVATE KEY-----
EOD;
        $response = $this->handleRequestWithAuthHeader(
            'Bearer ' . $this->authService->encodeBearerToken(1, ["clePrivee" => $mauvaiseClePrivee]),
            'GET',
            '/test-auth-middleware'
        );

        $this->assertEqualsResponse(200, 'null', $response);
    }
}
