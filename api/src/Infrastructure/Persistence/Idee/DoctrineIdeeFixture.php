<?php

namespace App\Infrastructure\Persistence\Idee;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use DateTime;
use DateTimeInterface;
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
                ->setDateProposition(new DateTime('8 hours ago'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('alice'))
                ->setDescription('une cravate')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(new DateTime('4 days ago'))
                ->setDateSuppression(new DateTime('10 hours ago'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('une canne à pêche')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(new DateTime('3 days ago'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('des gants de boxe')
                ->setAuteur($this->getReference('bob'))
                ->setDateProposition(new DateTime('5 minutes ago'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('charlie'))
                ->setDescription('un train électrique')
                ->setAuteur($this->getReference('charlie'))
                ->setDateProposition(new DateTime('1 hour ago'))
            );
            $em->persist((new DoctrineIdee())
                ->setUtilisateur($this->getReference('charlie'))
                ->setDescription('une brouette')
                ->setAuteur($this->getReference('bob'))
                ->setDateProposition(new DateTime('1 day ago'))
            );
            $em->flush();
        }
        $this->output->writeln(['Idées créées.']);
    }
}