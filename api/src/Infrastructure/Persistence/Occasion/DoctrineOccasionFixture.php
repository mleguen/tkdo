<?php

namespace App\Infrastructure\Persistence\Occasion;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class DoctrineOccasionFixture extends DoctrineAbstractFixture
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
                'noelPasse' => (new DoctrineOccasion())
                    ->setDate(new DateTime("$anneePassee-12-25"))
                    ->setTitre("Noël $anneePassee")
                    ->setParticipants([
                        $this->getReference('alice'),
                        $this->getReference('bob'),
                        $this->getReference('charlie'),
                        $this->getReference('david'),
                    ]),
                'noelProchain' => (new DoctrineOccasion())
                    ->setDate(new DateTime("$anneeProchaine-12-25"))
                    ->setTitre("Noël $anneeProchaine")
                    ->setDate(new DateTime('2020-12-25'))
                    ->setTitre('Noël 2020')
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