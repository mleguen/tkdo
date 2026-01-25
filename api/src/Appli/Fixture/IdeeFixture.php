<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class IdeeFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('alice', UtilisateurAdaptor::class))
                ->setDescription('un gauffrier')
                ->setAuteur($this->getReference('alice', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('8 hours ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('alice', UtilisateurAdaptor::class))
                ->setDescription('une cravate')
                ->setAuteur($this->getReference('alice', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('4 days ago'))
                ->setDateSuppression(new DateTime('10 hours ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('bob', UtilisateurAdaptor::class))
                ->setDescription('une canne à pêche')
                ->setAuteur($this->getReference('alice', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('3 days ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('bob', UtilisateurAdaptor::class))
                ->setDescription('des gants de boxe')
                ->setAuteur($this->getReference('bob', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('5 minutes ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('charlie', UtilisateurAdaptor::class))
                ->setDescription('un train électrique')
                ->setAuteur($this->getReference('charlie', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('1 hour ago'))
            );
            $em->persist(new IdeeAdaptor()
                ->setUtilisateur($this->getReference('charlie', UtilisateurAdaptor::class))
                ->setDescription('une brouette')
                ->setAuteur($this->getReference('bob', UtilisateurAdaptor::class))
                ->setDateProposition(new DateTime('1 day ago'))
            );
            $em->flush();

            // Perf mode: create 20+ ideas for bob
            if ($this->perfMode) {
                $bob = $this->getReference('bob', UtilisateurAdaptor::class);
                for ($i = 1; $i <= 22; $i++) {
                    $em->persist(new IdeeAdaptor()
                        ->setUtilisateur($bob)
                        ->setDescription("Idée perf test {$i}")
                        ->setAuteur($bob)
                        ->setDateProposition(new DateTime("-{$i} hours"))
                    );
                }
                $em->flush();
                $this->output->writeln(['  + 22 idées perf pour bob créées.']);
            }
        }
        $this->output->writeln(['Idées créées.']);
    }
}