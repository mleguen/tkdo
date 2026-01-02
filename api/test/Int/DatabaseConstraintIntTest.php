<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\ResultatAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class DatabaseConstraintIntTest extends IntTestCase
{
    // ========== UNIQUE CONSTRAINTS ==========

    public function testUtilisateurIdentifiantUnique(): void
    {
        $utilisateur1 = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $utilisateur2 = $this->utilisateur()->withIdentifiant($utilisateur1->getIdentifiant())->build();
        $utilisateur2->setMdpClair('password');

        try {
            self::$em->persist($utilisateur2);
            self::$em->flush();
            $this->fail('Expected UniqueConstraintViolationException was not thrown');
        } catch (UniqueConstraintViolationException $e) {
            // Expected exception - clear the closed EntityManager
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testResultatQuiRecoitUniqueParOccasion(): void
    {
        $occasion = $this->occasion()->persist(self::$em);
        $utilisateur1 = $this->utilisateur()->withIdentifiant('utilisateur1')->persist(self::$em);
        $utilisateur2 = $this->utilisateur()->withIdentifiant('utilisateur2')->persist(self::$em);
        $utilisateur3 = $this->utilisateur()->withIdentifiant('utilisateur3')->persist(self::$em);

        // Premier résultat: utilisateur1 offre à utilisateur3
        $this->resultat()
            ->forOccasion($occasion)
            ->withQuiOffre($utilisateur1)
            ->withQuiRecoit($utilisateur3)
            ->persist(self::$em);

        // Deuxième résultat: essaie de faire offrir utilisateur2 à utilisateur3 (même occasion, même destinataire)
        $resultat2 = $this->resultat()
            ->forOccasion($occasion)
            ->withQuiOffre($utilisateur2)
            ->withQuiRecoit($utilisateur3)
            ->build();

        try {
            self::$em->persist($resultat2);
            self::$em->flush();
            $this->fail('Expected UniqueConstraintViolationException was not thrown');
        } catch (UniqueConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    // ========== FOREIGN KEY CONSTRAINTS ==========
    // Note: Foreign key constraints are implicitly tested by the cascade deletion tests below.
    // Doctrine's ORM layer prevents direct testing of foreign key violations on insert
    // because it enforces entity persistence before allowing references.

    // ========== CASCADE DELETIONS ==========

    public function testDeleteUtilisateurWithIdeesFails(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $this->idee()->byAuteur($auteur)->forUtilisateur($utilisateur)->persist(self::$em);

        try {
            self::$em->remove($utilisateur);
            self::$em->flush();
            $this->fail('Expected ForeignKeyConstraintViolationException was not thrown');
        } catch (ForeignKeyConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDeleteAuteurWithIdeesFails(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $this->idee()->byAuteur($auteur)->forUtilisateur($utilisateur)->persist(self::$em);

        try {
            self::$em->remove($auteur);
            self::$em->flush();
            $this->fail('Expected ForeignKeyConstraintViolationException was not thrown');
        } catch (ForeignKeyConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDeleteUtilisateurWithResultatsFails(): void
    {
        $occasion = $this->occasion()->persist(self::$em);
        $utilisateur1 = $this->utilisateur()->withIdentifiant('utilisateur1')->persist(self::$em);
        $utilisateur2 = $this->utilisateur()->withIdentifiant('utilisateur2')->persist(self::$em);
        $this->resultat()
            ->forOccasion($occasion)
            ->withQuiOffre($utilisateur1)
            ->withQuiRecoit($utilisateur2)
            ->persist(self::$em);

        try {
            self::$em->remove($utilisateur1);
            self::$em->flush();
            $this->fail('Expected ForeignKeyConstraintViolationException was not thrown');
        } catch (ForeignKeyConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDeleteOccasionWithResultatsFails(): void
    {
        $occasion = $this->occasion()->persist(self::$em);
        $utilisateur1 = $this->utilisateur()->withIdentifiant('utilisateur1')->persist(self::$em);
        $utilisateur2 = $this->utilisateur()->withIdentifiant('utilisateur2')->persist(self::$em);
        $this->resultat()
            ->forOccasion($occasion)
            ->withQuiOffre($utilisateur1)
            ->withQuiRecoit($utilisateur2)
            ->persist(self::$em);

        try {
            self::$em->remove($occasion);
            self::$em->flush();
            $this->fail('Expected ForeignKeyConstraintViolationException was not thrown');
        } catch (ForeignKeyConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDeleteUtilisateurWithExclusionsFails(): void
    {
        $utilisateur1 = $this->utilisateur()->withIdentifiant('utilisateur1')->persist(self::$em);
        $utilisateur2 = $this->utilisateur()->withIdentifiant('utilisateur2')->persist(self::$em);

        $exclusion = new ExclusionAdaptor($utilisateur1, $utilisateur2);
        self::$em->persist($exclusion);
        self::$em->flush();

        try {
            self::$em->remove($utilisateur1);
            self::$em->flush();
            $this->fail('Expected ForeignKeyConstraintViolationException was not thrown');
        } catch (ForeignKeyConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDeleteUtilisateurWithoutRelationsSucceeds(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $id = $utilisateur->getId();

        self::$em->remove($utilisateur);
        self::$em->flush();

        $deleted = self::$em->find(UtilisateurAdaptor::class, $id);
        $this->assertNull($deleted);
    }

    public function testDeleteOccasionWithoutRelationsSucceeds(): void
    {
        $occasion = $this->occasion()->persist(self::$em);
        $id = $occasion->getId();

        self::$em->remove($occasion);
        self::$em->flush();

        $deleted = self::$em->find(OccasionAdaptor::class, $id);
        $this->assertNull($deleted);
    }

    // ========== NULL CONSTRAINTS ==========

    public function testIdeeUtilisateurCannotBeNull(): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);

        $idee = new IdeeAdaptor();
        $idee->setDescription('Test idée');
        $idee->setAuteur($auteur);
        $idee->setDateProposition(new \DateTime());

        try {
            self::$em->persist($idee);
            self::$em->flush();
            $this->fail('Expected NotNullConstraintViolationException was not thrown');
        } catch (NotNullConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testIdeeAuteurCannotBeNull(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $idee = new IdeeAdaptor();
        $idee->setDescription('Test idée');
        $idee->setUtilisateur($utilisateur);
        $idee->setDateProposition(new \DateTime());

        try {
            self::$em->persist($idee);
            self::$em->flush();
            $this->fail('Expected NotNullConstraintViolationException was not thrown');
        } catch (NotNullConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testResultatQuiRecoitCannotBeNull(): void
    {
        $occasion = $this->occasion()->persist(self::$em);
        $quiOffre = $this->utilisateur()->withIdentifiant('quiOffre')->persist(self::$em);

        $resultat = new ResultatAdaptor($occasion, $quiOffre);

        try {
            self::$em->persist($resultat);
            self::$em->flush();
            $this->fail('Expected NotNullConstraintViolationException was not thrown');
        } catch (NotNullConstraintViolationException $e) {
            if (!self::$em->isOpen()) {
                $this->resetEntityManager();
            }
            // Mark catching the exception as an assertion for PHPUnit's strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testIdeeDateSuppressionCanBeNull(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);

        $idee = $this->idee()->byAuteur($auteur)->forUtilisateur($utilisateur)->withDateSuppression(null)->persist(self::$em);

        $this->assertNull($idee->getDateSuppression());

        // Verify it was persisted correctly
        $id = $idee->getId();
        self::$em->clear();

        $retrieved = self::$em->find(IdeeAdaptor::class, $id);
        $this->assertNotNull($retrieved);
        $this->assertNull($retrieved->getDateSuppression());
    }
}
