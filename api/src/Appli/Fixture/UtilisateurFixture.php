<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class UtilisateurFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        require __DIR__ . '/noel_user.data.php';
        $utilisateurs = [];
        foreach ($noel_user as $row) {
            $complement = $noel_user_complement[$row[0]];
            $utilisateurs["u{$row[0]}"] = (new UtilisateurAdaptor())
                ->setEmail($row[3] ?: $complement['email'])
                ->setMdpClair($row[4])
                ->setIdentifiant($complement['identifiant'])
                ->setNom($complement['nom'])
                ->setGenre($complement['genre'])
                ->setDateDerniereNotifPeriodique(new DateTime());
        }
        foreach ($noel_user_complement_2 as $id => $complement) {
            $utilisateurs["u{$id}"] = (new UtilisateurAdaptor())
                ->setMdpClair($complement['mdpClair'])
                ->setIdentifiant($complement['identifiant'])
                ->setNom($complement['nom'])
                ->setGenre($complement['genre'])
                ->setEmail($complement['email'])
                ->setDateDerniereNotifPeriodique(new DateTime());
        }
        $utilisateurs['u1']->setAdmin(true);
        foreach ($utilisateurs as $nom => $utilisateur) {
            $em->persist($utilisateur);
            $this->addReference($nom, $utilisateur);
        }
        $em->flush();
        $this->output->writeln(['Utilisateurs créés.']);
    }
}
