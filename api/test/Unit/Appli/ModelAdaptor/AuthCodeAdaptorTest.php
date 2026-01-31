<?php

declare(strict_types=1);

namespace Test\Unit\Appli\ModelAdaptor;

use App\Appli\ModelAdaptor\AuthCodeAdaptor;
use DateTime;
use PHPUnit\Framework\TestCase;

class AuthCodeAdaptorTest extends TestCase
{
    public function testEstExpireReturnsFalseWhenNotExpired(): void
    {
        $authCode = new AuthCodeAdaptor();
        $authCode->setExpiresAt(new DateTime('+1 hour'));

        $this->assertFalse($authCode->estExpire());
    }

    public function testEstExpireReturnsTrueWhenExpired(): void
    {
        $authCode = new AuthCodeAdaptor();
        $authCode->setExpiresAt(new DateTime('-1 second'));

        $this->assertTrue($authCode->estExpire());
    }

    public function testEstUtiliseReturnsFalseWhenNotUsed(): void
    {
        $authCode = new AuthCodeAdaptor();

        $this->assertFalse($authCode->estUtilise());
    }

    public function testEstUtiliseReturnsTrueWhenUsed(): void
    {
        $authCode = new AuthCodeAdaptor();
        $authCode->setUsedAt(new DateTime());

        $this->assertTrue($authCode->estUtilise());
    }

    public function testVerifieCodeReturnsTrueForCorrectCode(): void
    {
        $authCode = new AuthCodeAdaptor();
        $authCode->setCodeHash('mon_code_secret');

        $this->assertTrue($authCode->verifieCode('mon_code_secret'));
    }

    public function testVerifieCodeReturnsFalseForIncorrectCode(): void
    {
        $authCode = new AuthCodeAdaptor();
        $authCode->setCodeHash('mon_code_secret');

        $this->assertFalse($authCode->verifieCode('mauvais_code'));
    }

    public function testSetCodeHashUsesSalt(): void
    {
        $code = 'same_code_for_both';

        $authCode1 = new AuthCodeAdaptor();
        $authCode1->setCodeHash($code);

        $authCode2 = new AuthCodeAdaptor();
        $authCode2->setCodeHash($code);

        // Both should verify the same code
        $this->assertTrue($authCode1->verifieCode($code));
        $this->assertTrue($authCode2->verifieCode($code));

        // But the stored hashes should be different (due to salting)
        $reflection = new \ReflectionClass(AuthCodeAdaptor::class);
        $codeHashProperty = $reflection->getProperty('codeHash');

        $hash1 = $codeHashProperty->getValue($authCode1);
        $hash2 = $codeHashProperty->getValue($authCode2);

        $this->assertNotEquals($hash1, $hash2, 'Hashes should differ due to salting');
    }

    public function testFluentSetters(): void
    {
        $authCode = new AuthCodeAdaptor();
        $expiresAt = new DateTime('+1 hour');
        $usedAt = new DateTime();

        $result = $authCode
            ->setUtilisateurId(42)
            ->setExpiresAt($expiresAt)
            ->setUsedAt($usedAt);

        $this->assertSame($authCode, $result);
        $this->assertEquals(42, $authCode->getUtilisateurId());
        $this->assertSame($expiresAt, $authCode->getExpiresAt());
        $this->assertSame($usedAt, $authCode->getUsedAt());
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $before = new DateTime();
        $authCode = new AuthCodeAdaptor();
        $after = new DateTime();

        $createdAt = $authCode->getCreatedAt();
        $this->assertGreaterThanOrEqual($before, $createdAt);
        $this->assertLessThanOrEqual($after, $createdAt);
    }
}
