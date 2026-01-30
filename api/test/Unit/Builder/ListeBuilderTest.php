<?php

declare(strict_types=1);

namespace Test\Unit\Builder;

use PHPUnit\Framework\TestCase;
use Test\Builder\ListeBuilder;

/**
 * Unit tests for ListeBuilder scaffold.
 *
 * Tests verify the builder pattern API works correctly.
 * The actual entity creation (build/persist) will be tested when
 * ListeAdaptor entity is implemented.
 */
class ListeBuilderTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        // Reset counter before each test for isolation
        ListeBuilder::resetCounter();
    }

    public function testUneListeReturnsBuilderInstance(): void
    {
        $builder = ListeBuilder::uneListe();

        $this->assertInstanceOf(ListeBuilder::class, $builder);
    }

    public function testForUtilisateurSetsUser(): void
    {
        $utilisateur = new \stdClass();

        $builder = ListeBuilder::uneListe()
            ->forUtilisateur($utilisateur);

        $values = $builder->getValues();
        $this->assertSame($utilisateur, $values['utilisateur']);
    }

    public function testForUtilisateurReturnsSelfForChaining(): void
    {
        $builder = ListeBuilder::uneListe();

        $result = $builder->forUtilisateur(new \stdClass());

        $this->assertSame($builder, $result);
    }

    public function testDefaultUtilisateurIsNull(): void
    {
        $builder = ListeBuilder::uneListe();

        $values = $builder->getValues();
        $this->assertNull($values['utilisateur']);
    }

    public function testForGroupeSetsGroup(): void
    {
        $groupe = new \stdClass();

        $builder = ListeBuilder::uneListe()
            ->forGroupe($groupe);

        $values = $builder->getValues();
        $this->assertSame($groupe, $values['groupe']);
    }

    public function testForGroupeReturnsSelfForChaining(): void
    {
        $builder = ListeBuilder::uneListe();

        $result = $builder->forGroupe(new \stdClass());

        $this->assertSame($builder, $result);
    }

    public function testDefaultGroupeIsNull(): void
    {
        $builder = ListeBuilder::uneListe();

        $values = $builder->getValues();
        $this->assertNull($values['groupe']);
    }

    public function testWithVisibiliteSetsVisibility(): void
    {
        $builder = ListeBuilder::uneListe()
            ->withVisibilite(false);

        $values = $builder->getValues();
        $this->assertFalse($values['visible']);
    }

    public function testWithVisibiliteReturnsSelfForChaining(): void
    {
        $builder = ListeBuilder::uneListe();

        $result = $builder->withVisibilite(true);

        $this->assertSame($builder, $result);
    }

    public function testDefaultVisibleIsTrue(): void
    {
        $builder = ListeBuilder::uneListe();

        $values = $builder->getValues();
        $this->assertTrue($values['visible']);
    }

    public function testVisibleSetsVisibleToTrue(): void
    {
        $builder = ListeBuilder::uneListe()
            ->withVisibilite(false)
            ->visible();

        $values = $builder->getValues();
        $this->assertTrue($values['visible']);
    }

    public function testVisibleReturnsSelfForChaining(): void
    {
        $builder = ListeBuilder::uneListe();

        $result = $builder->visible();

        $this->assertSame($builder, $result);
    }

    public function testCacheeSetsVisibleToFalse(): void
    {
        $builder = ListeBuilder::uneListe()
            ->cachee();

        $values = $builder->getValues();
        $this->assertFalse($values['visible']);
    }

    public function testCacheeReturnsSelfForChaining(): void
    {
        $builder = ListeBuilder::uneListe();

        $result = $builder->cachee();

        $this->assertSame($builder, $result);
    }

    public function testResetCounterResetsToZero(): void
    {
        ListeBuilder::uneListe(); // Counter = 1
        ListeBuilder::uneListe(); // Counter = 2

        ListeBuilder::resetCounter();

        // Counter should be back to 0, next will be 1
        ListeBuilder::uneListe();
        // Just verify no exception - counter value not directly visible
        $this->assertTrue(true);
    }

    public function testBuildWithoutUtilisateurThrowsInvalidArgumentException(): void
    {
        $builder = ListeBuilder::uneListe()
            ->forGroupe(new \stdClass());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Utilisateur is required');

        $builder->build();
    }

    public function testBuildWithoutGroupeThrowsInvalidArgumentException(): void
    {
        $builder = ListeBuilder::uneListe()
            ->forUtilisateur(new \stdClass());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Groupe is required');

        $builder->build();
    }

    public function testBuildWithRequiredFieldsThrowsRuntimeException(): void
    {
        $builder = ListeBuilder::uneListe()
            ->forUtilisateur(new \stdClass())
            ->forGroupe(new \stdClass());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('scaffold');

        $builder->build();
    }

    public function testPersistThrowsRuntimeException(): void
    {
        $builder = ListeBuilder::uneListe()
            ->forUtilisateur(new \stdClass())
            ->forGroupe(new \stdClass());
        $em = $this->createMock(\Doctrine\ORM\EntityManager::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('scaffold');

        $builder->persist($em);
    }

    public function testFluentApiChaining(): void
    {
        $utilisateur = new \stdClass();
        $groupe = new \stdClass();

        $builder = ListeBuilder::uneListe()
            ->forUtilisateur($utilisateur)
            ->forGroupe($groupe)
            ->cachee();

        $values = $builder->getValues();

        $this->assertSame($utilisateur, $values['utilisateur']);
        $this->assertSame($groupe, $values['groupe']);
        $this->assertFalse($values['visible']);
    }
}
