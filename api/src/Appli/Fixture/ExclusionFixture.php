<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use Doctrine\Persistence\ObjectManager;

class ExclusionFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        require __DIR__ . '/noel_restriction.data.php';
        foreach (array_merge($noel_restriction, $noel_restriction_complement) as $row) {
            $em->persist(
                new ExclusionAdaptor($this->getReference("u{$row[0]}"), $this->getReference("u{$row[1]}"))
            );
        }
        $em->flush();
        $this->output->writeln(['Exclusions créées.']);
    }
}
