<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class OccasionFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if (!$this->prod) {
            $annee = (new DateTime())->format('Y');
            if (new DateTime("$annee-12-25") > new DateTime('now')) {
                $anneeProchaine = $annee;
                $anneePassee = $annee-1;
            } else {
                $anneeProchaine = $annee+1;
                $anneePassee = $annee;
            }

            foreach([
                'noelPasse' => (new OccasionAdaptor())
                    ->setDate(new DateTime("$anneePassee-12-25"))
                    ->setTitre("Noël $anneePassee")
                    ->setParticipants([
                        $this->getReference('alice'),
                        $this->getReference('bob'),
                        $this->getReference('charlie'),
                        $this->getReference('david'),
                    ]),
                'noelProchain' => (new OccasionAdaptor())
                    ->setDate(new DateTime("$anneeProchaine-12-25"))
                    ->setTitre("Noël $anneeProchaine")
                    ->setParticipants([
                        $this->getReference('alice'),
                        $this->getReference('bob'),
                        $this->getReference('charlie'),
                    ]),
            ] as $nom => $occasion) {
                $em->persist($occasion);
                $this->addReference($nom, $occasion);
            }
            $em->flush();
        }
        $this->output->writeln(['Occasions créées.']);
    }
}