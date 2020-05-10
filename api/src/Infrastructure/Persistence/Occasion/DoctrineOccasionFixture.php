<?php

namespace App\Infrastructure\Persistence\Occasion;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineOccasionFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        foreach([
            'occasion' => (new DoctrineOccasion())
                ->setTitre('Noël 2020')
                ->setParticipants([
                    $this->getReference('alice'),
                    $this->getReference('bob'),
                    $this->getReference('charlie'),
                    $this->getReference('david'),
                ]),
        ] as $nom => $occasion) {
            $em->persist($occasion);
            $this->addReference($nom, $occasion);
        }
        $em->flush();
        $this->output->writeln(['Occasions créées.']);
    }
}