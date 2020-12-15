<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Dom\Model\Genre;
use App\Dom\Model\PrefNotifIdees;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\OutputInterface;

class UtilisateurFixture extends AppAbstractFixture
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
                'admin' => (new UtilisateurAdaptor())
                    ->setEmail($this->adminEmail ?? "admin@$this->host")
                    ->setAdmin(true)
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('admin')
                    ->setMdpClair('admin')
                    ->setNom('Administrateur')
                    ->setDateDerniereNotifPeriodique(new DateTime()),
            ];
        } else {
            $utilisateurs = [
                'alice' => (new UtilisateurAdaptor())
                    ->setEmail("alice@$this->host")
                    ->setAdmin(true)
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('alice')
                    ->setNom('Alice')
                    ->setMdpClair('mdpalice')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'bob' => (new UtilisateurAdaptor())
                    ->setEmail("bob@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('bob')
                    ->setNom('Bob')
                    ->setMdpClair('mdpbob')
                    ->setPrefNotifIdees(PrefNotifIdees::Instantanee)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'charlie' => (new UtilisateurAdaptor())
                    ->setEmail("charlie@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('charlie')
                    ->setMdpClair('mdpcharlie')
                    ->setNom('Charlie')
                    ->setPrefNotifIdees(PrefNotifIdees::Quotidienne)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'david' => (new UtilisateurAdaptor())
                    ->setEmail("david@$this->host")
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('david')
                    ->setMdpClair('mdpdavid')
                    ->setNom('David')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'eve' => (new UtilisateurAdaptor())
                    ->setEmail("eve@$this->host")
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('eve')
                    ->setMdpClair('mdpeve')
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