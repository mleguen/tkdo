<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\ResultatAdaptor;
use Doctrine\Persistence\ObjectManager;

class ResultatFixture extends AppAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if ($this->devMode) {
            foreach ([
                'noelPasse' => [
                    'alice'   => 'bob',
                    'bob'     => 'david',
                    'charlie' => 'alice',
                    'david'   => 'charlie',
                ],
                'noelProchain' => [
                    'alice'   => 'charlie',
                    'bob'     => 'alice',
                    'charlie' => 'bob',
                    // David ne participe pas à Noël 2020
                ],
                // Eve ne participe à aucune occasion
            ] as $nomOccasion => $tirage) {
                foreach ($tirage as $nomQuiDonne => $nomQuiRecoit) {
                    $em->persist(
                        new ResultatAdaptor($this->getReference($nomOccasion), $this->getReference($nomQuiDonne))
                            ->setQuiRecoit($this->getReference($nomQuiRecoit))
                    );
                }
            }
            
            $em->flush();
        }
        $this->output->writeln(['Résultats créés.']);
    }
}