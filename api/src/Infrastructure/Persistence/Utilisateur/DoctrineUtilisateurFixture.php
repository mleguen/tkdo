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
                ->setMdp(hash('sha256', 'mdpalice')),
            'bob' => (new DoctrineUtilisateur())
                ->setIdentifiant('bob@tkdo.org')
                ->setNom('Bob')
                ->setMdp(hash('sha256', 'mdpbob')),
            'charlie' => (new DoctrineUtilisateur())
                ->setIdentifiant('charlie@tkdo.org')
                ->setNom('Charlie')
                ->setMdp(hash('sha256', 'mdpcharlie')),
            'david' => (new DoctrineUtilisateur())
                ->setIdentifiant('david@tkdo.org')
                ->setNom('David')
                ->setMdp(hash('sha256', 'mdpdavid')),
        ] as $nom => $utilisateur) {
            $em->persist($utilisateur);
            $this->addReference($nom, $utilisateur);
        }
        $em->flush();
        $this->output->writeln(['Utilisateurs créés.']);
   }
}