<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\GroupeAdaptor;
use App\Dom\Exception\GroupeInconnuException;
use App\Dom\Model\Groupe;
use App\Dom\Repository\GroupeRepository;
use DateTime;
use Test\Builder\GroupeBuilder;
use Test\Builder\UtilisateurBuilder;

class GroupeRepositoryTest extends IntTestCase
{
    private GroupeRepository $repo;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->repo = new \App\Appli\RepositoryAdaptor\GroupeRepositoryAdaptor(self::$em);
    }

    public function testCreatePersistsGroupeAndReturnsEntityWithGeneratedId(): void
    {
        $groupe = $this->repo->create('Famille Dupont');

        $this->assertInstanceOf(Groupe::class, $groupe);
        $this->assertGreaterThan(0, $groupe->getId());
        $this->assertEquals('Famille Dupont', $groupe->getNom());
        $this->assertFalse($groupe->getArchive());
        $this->assertInstanceOf(DateTime::class, $groupe->getDateCreation());
    }

    public function testCreateThrowsOnEmptyNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo->create('');
    }

    public function testCreateThrowsOnWhitespaceOnlyNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo->create('   ');
    }

    public function testReadRetrievesById(): void
    {
        $created = $this->repo->create('Les Amis');
        self::$em->clear();

        $read = $this->repo->read($created->getId());

        $this->assertEquals($created->getId(), $read->getId());
        $this->assertEquals('Les Amis', $read->getNom());
    }

    public function testReadThrowsGroupeInconnuExceptionForUnknownId(): void
    {
        $this->expectException(GroupeInconnuException::class);

        $this->repo->read(99999);
    }

    public function testReadAllReturnsAllGroupes(): void
    {
        $this->repo->create('Groupe A');
        $this->repo->create('Groupe B');
        $this->repo->create('Groupe C');

        $all = $this->repo->readAll();

        $this->assertCount(3, $all);
        $noms = array_map(fn(Groupe $g) => $g->getNom(), $all);
        $this->assertContains('Groupe A', $noms);
        $this->assertContains('Groupe B', $noms);
        $this->assertContains('Groupe C', $noms);
    }

    public function testUpdatePersistsChanges(): void
    {
        $groupe = $this->repo->create('Original');
        $groupe->setNom('Modifié');

        $updated = $this->repo->update($groupe);
        self::$em->clear();

        $reloaded = $this->repo->read($updated->getId());
        $this->assertEquals('Modifié', $reloaded->getNom());
    }

    public function testUpdateThrowsOnEmptyNom(): void
    {
        $groupe = $this->repo->create('Valid Name');
        $groupe->setNom('');

        $this->expectException(\InvalidArgumentException::class);

        $this->repo->update($groupe);
    }

    public function testUpdateThrowsOnWhitespaceOnlyNom(): void
    {
        $groupe = $this->repo->create('Valid Name');
        $groupe->setNom('   ');

        $this->expectException(\InvalidArgumentException::class);

        $this->repo->update($groupe);
    }

    public function testAppartenanceCanBeCreatedAndLinked(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        $groupe = GroupeBuilder::unGroupe()
            ->withNom('Famille Test')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        self::$em->clear();

        /** @var GroupeAdaptor $reloaded */
        $reloaded = $this->repo->read($groupe->getId());
        $appartenances = $reloaded->getAppartenances();

        $this->assertCount(1, $appartenances);
        $this->assertEquals($utilisateur->getId(), $appartenances[0]->getUtilisateur()->getId());
        $this->assertTrue($appartenances[0]->getEstAdmin());
    }

    public function testReadAppartenancesForUtilisateurReturnsActiveGroupMemberships(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Actif A')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Actif B')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        self::$em->clear();

        $appartenances = $this->repo->readAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(2, $appartenances);
    }

    public function testReadAppartenancesForUtilisateurExcludesArchivedGroups(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Actif')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Archivé')
            ->withArchive(true)
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        self::$em->clear();

        $appartenances = $this->repo->readAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(1, $appartenances);
        $this->assertEquals('Groupe Actif', $appartenances[0]->getGroupe()->getNom());
    }

    public function testReadAppartenancesForUtilisateurWithNoGroupsReturnsEmpty(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);

        $appartenances = $this->repo->readAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(0, $appartenances);
    }

    public function testReadToutesAppartenancesForUtilisateurReturnsActiveAndArchived(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Actif')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Archivé')
            ->withArchive(true)
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        self::$em->clear();

        $appartenances = $this->repo->readToutesAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(2, $appartenances);
        // Verify alphabetical sort order (orderBy g.nom ASC)
        $this->assertEquals('Groupe Actif', $appartenances[0]->getGroupe()->getNom());
        $this->assertEquals('Groupe Archivé', $appartenances[1]->getGroupe()->getNom());
    }

    public function testReadToutesAppartenancesForUtilisateurWithNoGroupsReturnsEmpty(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);

        $appartenances = $this->repo->readToutesAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(0, $appartenances);
    }

    public function testReadToutesAppartenancesForUtilisateurPreservesAdminFlag(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Admin')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Membre')
            ->withArchive(true)
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        self::$em->clear();

        $appartenances = $this->repo->readToutesAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(2, $appartenances);
        // Verify alphabetical sort order (orderBy g.nom ASC): 'Groupe Admin' < 'Groupe Membre'
        $this->assertEquals('Groupe Admin', $appartenances[0]->getGroupe()->getNom());
        $this->assertTrue($appartenances[0]->getEstAdmin());
        $this->assertEquals('Groupe Membre', $appartenances[1]->getGroupe()->getNom());
        $this->assertFalse($appartenances[1]->getEstAdmin());
    }

    public function testReadAppartenancesForUtilisateurPreservesAdminFlag(): void
    {
        $utilisateur = UtilisateurBuilder::aUser()->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Admin')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Groupe Membre')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        self::$em->clear();

        $appartenances = $this->repo->readAppartenancesForUtilisateur($utilisateur->getId());

        $this->assertCount(2, $appartenances);
        $adminFlags = [];
        foreach ($appartenances as $a) {
            $adminFlags[$a->getGroupe()->getNom()] = $a->getEstAdmin();
        }
        $this->assertTrue($adminFlags['Groupe Admin']);
        $this->assertFalse($adminFlags['Groupe Membre']);
    }
}
