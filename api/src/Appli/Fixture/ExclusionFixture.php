<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use Doctrine\Persistence\ObjectManager;

class ExclusionFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            $em->persist(new ExclusionAdaptor(
                $this->getReference('alice', UtilisateurAdaptor::class),
                $this->getReference('bob', UtilisateurAdaptor::class),
            ));
            $em->flush();
        }
        $this->output->writeln(['Exclusions créées.']);
    }
}
