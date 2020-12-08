<?php

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Genre;
use App\Domain\Utilisateur\PrefNotifIdees;
use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\OutputInterface;

class DoctrineUtilisateurFixture extends DoctrineAbstractFixture
{
    private $adminEmail;
    private $host;

    public function __construct(OutputInterface $output, bool $prod, string $host, string $adminEmail = null)
    {
        parent::__construct($output, $prod);
        $this->host = $host;
        $this->adminEmail = $adminEmail;
    }

    public function load(ObjectManager $em)
    {
        if ($this->prod) {
            $utilisateurs = [
                'admin' => (new DoctrineUtilisateur())
                    ->setEmail($this->adminEmail ?? "admin@$this->host")
                    ->setEstAdmin(true)
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('admin')
                    ->setMdp(password_hash('admin', PASSWORD_DEFAULT))
                    ->setNom('Administrateur')
                    ->setDateDerniereNotifPeriodique(new DateTime()),
            ];
        } else {
            $utilisateurs = [
                'alice' => (new DoctrineUtilisateur())
                    ->setEmail("alice@$this->host")
                    ->setEstAdmin(true)
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('alice')
                    ->setNom('Alice')
                    ->setMdp(password_hash('mdpalice', PASSWORD_DEFAULT))
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'bob' => (new DoctrineUtilisateur())
                    ->setEmail("bob@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('bob')
                    ->setNom('Bob')
                    ->setMdp(password_hash('mdpbob', PASSWORD_DEFAULT))
                    ->setPrefNotifIdees(PrefNotifIdees::Instantanee)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'charlie' => (new DoctrineUtilisateur())
                    ->setEmail("charlie@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('charlie')
                    ->setMdp(password_hash('mdpcharlie', PASSWORD_DEFAULT))
                    ->setNom('Charlie')
                    ->setPrefNotifIdees(PrefNotifIdees::Quotidienne)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'david' => (new DoctrineUtilisateur())
                    ->setEmail("david@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('david')
                    ->setMdp(password_hash('mdpdavid', PASSWORD_DEFAULT))
                    ->setNom('David')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'eve' => (new DoctrineUtilisateur())
                    ->setEmail("eve@$this->host")
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('eve')
                    ->setMdp(password_hash('mdpeve', PASSWORD_DEFAULT))
                    ->setNom('Eve')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
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