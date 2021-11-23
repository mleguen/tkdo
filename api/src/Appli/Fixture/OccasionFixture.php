<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class OccasionFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        require __DIR__ . '/noel_repartition.data.php';
        $annees = [];
        foreach ($noel_repartition as $row) {
            if (!array_key_exists($row[0], $annees)) {
                $annees[$row[0]] = [];
            }
            foreach ([$row[1], $row[2]] as $participant) {
                $participant = $this->getReference("u$participant");
                if (!in_array($participant, $annees[$row[0]])) {
                    $annees[$row[0]][] = $participant;
                }
            }
        }
        $that = $this;
        foreach ($noel_repartition_complement as $annee => $participants) {
            $annees[$annee] = array_map(
                function ($participant) use ($that) {
                    return $that->getReference("u$participant");
                },
                $participants
            );
        }
        foreach ($annees as $annee => $participants) {
            $occasion = (new OccasionAdaptor())
                ->setDate(new DateTime("$annee-12-24"))
                ->setTitre("Noël $annee")
                ->setParticipants($participants);
            $em->persist($occasion);
            $this->addReference("o$annee", $occasion);
        }
        $em->flush();
        $this->output->writeln(['Occasions créées.']);
    }
}
