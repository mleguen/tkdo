<?php

namespace App\Infrastructure\Persistence\Resultat;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineResultatFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        $em->persist(
            (new DoctrineResultat($this->getReference('occasion'), $this->getReference('alice')))
                ->setQuiRecoit($this->getReference('bob'))
        );
        $em->flush();
        $this->output->writeln(['Résultats créés.']);
    }
}