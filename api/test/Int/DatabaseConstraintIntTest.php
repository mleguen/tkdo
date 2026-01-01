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
        $utilisateur1 = $this->creeUtilisateurEnBase('utilisateur');
        $utilisateur2 = $this->creeUtilisateurEnMemoire('utilisateur2');
        $utilisateur2->setIdentifiant($utilisateur1->getIdentifiant());
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
        $occasion = $this->creeOccasionEnBase();
        $utilisateur1 = $this->creeUtilisateurEnBase('utilisateur1');
        $utilisateur2 = $this->creeUtilisateurEnBase('utilisateur2');
        $utilisateur3 = $this->creeUtilisateurEnBase('utilisateur3');

        // Premier résultat: utilisateur1 offre à utilisateur3
        $resultat1 = $this->creeResultatEnBase($occasion, $utilisateur1, $utilisateur3);

        // Deuxième résultat: essaie de faire offrir utilisateur2 à utilisateur3 (même occasion, même destinataire)
        $resultat2 = $this->creeResultatEnMemoire($occasion, $utilisateur2, $utilisateur3);

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
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $this->creeIdeeEnBase([
            'utilisateur' => $utilisateur,
            'auteur' => $auteur,
        ]);

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
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $this->creeIdeeEnBase([
            'utilisateur' => $utilisateur,
            'auteur' => $auteur,
        ]);

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
        $occasion = $this->creeOccasionEnBase();
        $utilisateur1 = $this->creeUtilisateurEnBase('utilisateur1');
        $utilisateur2 = $this->creeUtilisateurEnBase('utilisateur2');
        $this->creeResultatEnBase($occasion, $utilisateur1, $utilisateur2);

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
        $occasion = $this->creeOccasionEnBase();
        $utilisateur1 = $this->creeUtilisateurEnBase('utilisateur1');
        $utilisateur2 = $this->creeUtilisateurEnBase('utilisateur2');
        $this->creeResultatEnBase($occasion, $utilisateur1, $utilisateur2);

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
        $utilisateur1 = $this->creeUtilisateurEnBase('utilisateur1');
        $utilisateur2 = $this->creeUtilisateurEnBase('utilisateur2');

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
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $id = $utilisateur->getId();

        self::$em->remove($utilisateur);
        self::$em->flush();

        $deleted = self::$em->find(UtilisateurAdaptor::class, $id);
        $this->assertNull($deleted);
    }

    public function testDeleteOccasionWithoutRelationsSucceeds(): void
    {
        $occasion = $this->creeOccasionEnBase();
        $id = $occasion->getId();

        self::$em->remove($occasion);
        self::$em->flush();

        $deleted = self::$em->find(OccasionAdaptor::class, $id);
        $this->assertNull($deleted);
    }

    // ========== NULL CONSTRAINTS ==========

    public function testIdeeUtilisateurCannotBeNull(): void
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');

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
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

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
        $occasion = $this->creeOccasionEnBase();
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');

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
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $auteur = $this->creeUtilisateurEnBase('auteur');

        $idee = $this->creeIdeeEnBase([
            'utilisateur' => $utilisateur,
            'auteur' => $auteur,
            'dateSuppression' => null,
        ]);

        $this->assertNull($idee->getDateSuppression());

        // Verify it was persisted correctly
        $id = $idee->getId();
        self::$em->clear();

        $retrieved = self::$em->find(IdeeAdaptor::class, $id);
        $this->assertNotNull($retrieved);
        $this->assertNull($retrieved->getDateSuppression());
    }
}
