<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use Doctrine\Persistence\ObjectManager;

class ExclusionFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            $em->persist(new ExclusionAdaptor(
                $this->getReference('alice'),
                $this->getReference('bob'),
            ));
            $em->flush();
        }
        $this->output->writeln(['Exclusions créées.']);
    }
}
