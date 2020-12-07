<?php

namespace App\Infrastructure\Persistence\Resultat;

use App\Infrastructure\Persistence\DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DoctrineResultatFixture extends DoctrineAbstractFixture
{
    public function load(ObjectManager $em)
    {
        if (!$this->prod) {
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
                        (new DoctrineResultat($this->getReference($nomOccasion), $this->getReference($nomQuiDonne)))
                            ->setQuiRecoit($this->getReference($nomQuiRecoit))
                    );
                }
            }
            
            $em->flush();
        }
        $this->output->writeln(['Résultats créés.']);
    }
}