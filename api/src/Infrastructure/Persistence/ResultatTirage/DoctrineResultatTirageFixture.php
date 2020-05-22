<?php

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineResultatTirageFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        $em->persist(
            (new DoctrineResultatTirage($this->getReference('occasion'), $this->getReference('alice')))
                ->setQuiRecoit($this->getReference('bob'))
        );
        $em->flush();
        $this->output->writeln(['Résultats de tirage créés.']);
    }
}