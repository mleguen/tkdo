<?php

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineUtilisateurFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        foreach([
            'alice' => (new DoctrineUtilisateur())
                ->setIdentifiant('alice@tkdo.org')
                ->setNom('Alice')
                ->setMdp(password_hash('mdpalice', PASSWORD_DEFAULT)),
            'bob' => (new DoctrineUtilisateur())
                ->setIdentifiant('bob@tkdo.org')
                ->setNom('Bob')
                ->setMdp(password_hash('mdpbob', PASSWORD_DEFAULT)),
            'charlie' => (new DoctrineUtilisateur())
                ->setIdentifiant('charlie@tkdo.org')
                ->setNom('Charlie')
                ->setMdp(password_hash('mdpcharlie', PASSWORD_DEFAULT)),
            'david' => (new DoctrineUtilisateur())
                ->setIdentifiant('david@tkdo.org')
                ->setNom('David')
                ->setMdp(password_hash('mdpdavid', PASSWORD_DEFAULT)),
        ] as $nom => $utilisateur) {
            $em->persist($utilisateur);
            $this->addReference($nom, $utilisateur);
        }
        $em->flush();
        $this->output->writeln(['Utilisateurs créés.']);
   }
}