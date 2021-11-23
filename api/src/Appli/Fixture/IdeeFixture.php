<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class IdeeFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        require __DIR__ . '/noel_ideecadeau.data.php';
        foreach ($noel_ideecadeau as $row) {
            $em->persist(
                (new IdeeAdaptor())
                    ->setAuteur($this->getReference("u{$row[1]}"))
                    ->setUtilisateur($this->getReference("u{$row[2]}"))
                    ->setDateProposition(new DateTime($row[3]))
                    ->setDescription($row[4])
            );
        }
        $em->flush();
        $this->output->writeln(['Idées créées.']);
    }
}
