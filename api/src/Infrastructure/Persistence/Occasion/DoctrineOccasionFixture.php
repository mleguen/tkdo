<?php

namespace App\Infrastructure\Persistence\Occasion;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineOccasionFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if (!$this->prod) {
            foreach([
                'noel2019' => (new DoctrineOccasion())
                    ->setTitre('Noël 2019')
                    ->setParticipants([
                        $this->getReference('alice'),
                        $this->getReference('bob'),
                        $this->getReference('charlie'),
                        $this->getReference('david'),
                    ]),
                'noel2020' => (new DoctrineOccasion())
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