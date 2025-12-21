<?php

namespace App\Appli\Fixture;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\ResultatAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
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
                        new ResultatAdaptor(
                            $this->getReference($nomOccasion, OccasionAdaptor::class),
                            $this->getReference($nomQuiDonne, UtilisateurAdaptor::class)
                        )->setQuiRecoit($this->getReference($nomQuiRecoit, UtilisateurAdaptor::class))
                    );
                }
            }
            
            $em->flush();
        }
        $this->output->writeln(['Résultats créés.']);
    }
}