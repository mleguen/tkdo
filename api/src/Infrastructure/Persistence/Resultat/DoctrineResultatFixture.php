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
        $em->persist(
            (new DoctrineResultat($this->getReference('occasion'), $this->getReference('bob')))
                ->setQuiRecoit($this->getReference('david'))
        );
        $em->persist(
            (new DoctrineResultat($this->getReference('occasion'), $this->getReference('charlie')))
                ->setQuiRecoit($this->getReference('alice'))
        );
        $em->persist(
            (new DoctrineResultat($this->getReference('occasion'), $this->getReference('david')))
                ->setQuiRecoit($this->getReference('charlie'))
        );
        $em->flush();
        $this->output->writeln(['Résultats créés.']);
    }
}