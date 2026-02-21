<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Appli\Service\UriService;
use App\Bootstrap;
use App\Dom\Model\Genre;
use App\Dom\Model\PrefNotifIdees;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class UtilisateurFixture extends AppAbstractFixture
{
    private ?string $adminEmail = null;

    public function __construct(
        Bootstrap $bootstrap,
        private readonly UriService $uriService
    ) {
        parent::__construct($bootstrap);
    }

    public function setAdminEmail(?string $adminEmail = null): self
    {
        $this->adminEmail = $adminEmail;
        return $this;
    }

    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            $utilisateurs = [
                'alice' => new UtilisateurAdaptor()
                    ->setEmail('alice@example.com')
                    ->setAdmin(true)
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('alice')
                    ->setNom('Alice')
                    ->setMdpClair('mdpalice')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'bob' => new UtilisateurAdaptor()
                    ->setEmail('bob@example.com')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('bob')
                    ->setNom('Bob')
                    ->setMdpClair('mdpbob')
                    ->setPrefNotifIdees(PrefNotifIdees::Instantanee)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'charlie' => new UtilisateurAdaptor()
                    ->setEmail('charlie@example.com')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('charlie')
                    ->setMdpClair('mdpcharlie')
                    ->setNom('Charlie')
                    ->setPrefNotifIdees(PrefNotifIdees::Quotidienne)
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'david' => new UtilisateurAdaptor()
                    ->setEmail('david@example.com')
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('david')
                    ->setMdpClair('mdpdavid')
                    ->setNom('David')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
                'eve' => new UtilisateurAdaptor()
                    ->setEmail('eve@example.com')
                    ->setGenre(Genre::Feminin)
                    ->setIdentifiant('eve')
                    ->setMdpClair('mdpeve')
                    ->setNom('Eve')
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago')),
            ];
        } else {
            $utilisateurs = [
                'admin' => new UtilisateurAdaptor()
                    ->setEmail($this->adminEmail ?? 'admin@' . $this->uriService->getHost())
                    ->setAdmin(true)
                    ->setGenre(Genre::Masculin)
                    ->setIdentifiant('admin')
                    ->setMdpClair('admin')
                    ->setNom('Administrateur')
                    ->setDateDerniereNotifPeriodique(new DateTime()),
            ];
        }
        foreach($utilisateurs as $nom => $utilisateur) {
            $em->persist($utilisateur);
            $this->addReference($nom, $utilisateur);
        }
        $em->flush();
        $this->output->writeln(['Utilisateurs créés.']);

        // Perf mode: create additional users to reach 10+ participants
        if ($this->devMode && $this->perfMode) {
            for ($i = 1; $i <= 6; $i++) {
                $user = new UtilisateurAdaptor();
                $user->setEmail("perf{$i}@example.com")
                    ->setGenre($i % 2 === 0 ? Genre::Feminin : Genre::Masculin)
                    ->setIdentifiant("perf{$i}")
                    ->setNom("Perf User {$i}")
                    ->setMdpClair("mdpperf{$i}")
                    ->setDateDerniereNotifPeriodique(new DateTime('2 days ago'));
                $em->persist($user);
                $this->addReference("perf{$i}", $user);
            }
            $em->flush();
            $this->output->writeln(['  + 6 utilisateurs perf créés.']);
        }
   }
}