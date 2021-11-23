<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\ResultatAdaptor;
use Doctrine\Persistence\ObjectManager;

class ResultatFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        require __DIR__ . '/noel_repartition.data.php';
        foreach ($noel_repartition as $row) {
            $em->persist(
                (new ResultatAdaptor($this->getReference("o{$row[0]}"), $this->getReference("u{$row[1]}")))
                    ->setQuiRecoit($this->getReference("u{$row[2]}"))
            );
        }
        $em->flush();
        $this->output->writeln(['Résultats créés.']);
    }
}
