<?php

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Genre;
use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\OutputInterface;

class DoctrineUtilisateurFixture extends DoctrineAbstractFixture
{
    public function __construct(OutputInterface $output, bool $prod, string $adminEmail = null)
    {
        parent::__construct($output, $prod);
        if ($prod && !isset($adminEmail)) throw new \Exception('email administrateur manquant');
        $this->adminEmail = $adminEmail;
    }

    public function load(ObjectManager $em)
    {
        if ($this->prod) {
            $utilisateurs = [
                'admin' => (new DoctrineUtilisateur())
                    ->setEmail($this->adminEmail)
                    ->setEstAdmin(true)
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('admin')
                    ->setMdp(password_hash('admin', PASSWORD_DEFAULT))
                    ->setNom('Administrateur'),
            ];
        } else {
            $utilisateurs = [
                'alice' => (new DoctrineUtilisateur())
                    ->setEmail('alice@tkdo.org')
                    ->setEstAdmin(true)
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('alice')
                    ->setNom('Alice')
                    ->setMdp(password_hash('mdpalice', PASSWORD_DEFAULT)),
                'bob' => (new DoctrineUtilisateur())
                    ->setEmail('bob@tkdo.org')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('bob')
                    ->setNom('Bob')
                    ->setMdp(password_hash('mdpbob', PASSWORD_DEFAULT)),
                'charlie' => (new DoctrineUtilisateur())
                    ->setEmail('charlie@tkdo.org')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('charlie')
                    ->setMdp(password_hash('mdpcharlie', PASSWORD_DEFAULT))
                    ->setNom('Charlie'),
                'david' => (new DoctrineUtilisateur())
                    ->setEmail('david@tkdo.org')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('david')
                    ->setMdp(password_hash('mdpdavid', PASSWORD_DEFAULT))
                    ->setNom('David'),
                'eve' => (new DoctrineUtilisateur())
                    ->setEmail('eve@tkdo.org')
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('eve')
                    ->setMdp(password_hash('mdpeve', PASSWORD_DEFAULT))
                    ->setNom('Eve'),
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