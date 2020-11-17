<?php

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Genre;
use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineUtilisateurFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->prod) {
            $utilisateurs = [
                'admin' => (new DoctrineUtilisateur())
                    ->setIdentifiant('admin')
                    ->setNom('Administrateur')
                    ->setMdp(password_hash('admin', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Masculin)
                    ->setEstAdmin(true),
            ];
        } else {
            $utilisateurs = [
                'alice' => (new DoctrineUtilisateur())
                    ->setIdentifiant('alice@tkdo.org')
                    ->setNom('Alice')
                    ->setMdp(password_hash('mdpalice', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Feminin)
                    ->setEstAdmin(true),
                'bob' => (new DoctrineUtilisateur())
                    ->setIdentifiant('bob@tkdo.org')
                    ->setNom('Bob')
                    ->setMdp(password_hash('mdpbob', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Masculin),
                'charlie' => (new DoctrineUtilisateur())
                    ->setIdentifiant('charlie@tkdo.org')
                    ->setNom('Charlie')
                    ->setMdp(password_hash('mdpcharlie', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Masculin),
                'david' => (new DoctrineUtilisateur())
                    ->setIdentifiant('david@tkdo.org')
                    ->setNom('David')
                    ->setMdp(password_hash('mdpdavid', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Masculin),
                'eve' => (new DoctrineUtilisateur())
                    ->setIdentifiant('eve@tkdo.org')
                    ->setNom('Eve')
                    ->setMdp(password_hash('mdpeve', PASSWORD_DEFAULT))
                    ->setGenre(Genre::Feminin),
            ];
        }
        foreach($utilisateurs as $nom => $utilisateur) {
            $em->persist($utilisateur);
            $this->addReference($nom, $utilisateur);
        }
        $em->flush();
        $this->output->writeln(['Utilisateurs créés.']);
   }
}