<?php

namespace App\Infrastructure\Persistence\Idee;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineIdeeFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if (!$this->prod) {
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('alice'))
                ->setDescription('un gauffrier')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('une canne à pêche')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('des gants de boxe')
                ->setAuteur($this->getReference('bob'))
                ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-07T00:00:00+0000'))
            );
            $em->flush();
        }
        $this->output->writeln(['Idées créées.']);
    }
}