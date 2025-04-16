<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class IdeeFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('alice'))
                ->setDescription('un gauffrier')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(new DateTime('8 hours ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('alice'))
                ->setDescription('une cravate')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(new DateTime('4 days ago'))
                ->setDateSuppression(new DateTime('10 hours ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('une canne à pêche')
                ->setAuteur($this->getReference('alice'))
                ->setDateProposition(new DateTime('3 days ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('bob'))
                ->setDescription('des gants de boxe')
                ->setAuteur($this->getReference('bob'))
                ->setDateProposition(new DateTime('5 minutes ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('charlie'))
                ->setDescription('un train électrique')
                ->setAuteur($this->getReference('charlie'))
                ->setDateProposition(new DateTime('1 hour ago'))
            );
            $em->persist(new IdeeAdaptor()
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