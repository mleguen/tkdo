<?php

declare(strict_types=1);

namespace App\Appli\Fixture;

use App\Bootstrap;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture for creating test Groupe entities
 *
 * SCAFFOLD: This fixture is a placeholder for v2 Groupe entity support.
 * The actual GroupeAdaptor entity will be created in Story 2.1.
 * Update this fixture when the entity is implemented.
 *
 * Planned data:
 * - DevMode: 2 groups (Famille, Amis) with user memberships
 * - PerfMode: Additional groups for performance testing
 */
class GroupeFixture extends AppAbstractFixture
{
    public function __construct(
        Bootstrap $bootstrap
    ) {
        parent::__construct($bootstrap);
    }

    #[\Override]
    public function load(ObjectManager $em): void
    {
        if ($this->devMode) {
            // TODO: Implement when GroupeAdaptor entity is created in Story 2.1
            //
            // Planned groups:
            // - 'famille': Groupe with alice, bob, charlie as members
            // - 'amis': Groupe with bob, david, eve as members
            //
            // Example implementation:
            // $groupeFamille = new GroupeAdaptor()
            //     ->setNom('Famille')
            //     ->setDescription('Groupe familial');
            // $em->persist($groupeFamille);
            // $this->addReference('famille', $groupeFamille);
            //
            // // Add members
            // $groupeFamille->addMembre($this->getReference('alice', UtilisateurAdaptor::class));
            // $groupeFamille->addMembre($this->getReference('bob', UtilisateurAdaptor::class));
            // $groupeFamille->addMembre($this->getReference('charlie', UtilisateurAdaptor::class));
            //
            // $groupeAmis = new GroupeAdaptor()
            //     ->setNom('Amis')
            //     ->setDescription('Groupe d\'amis');
            // $em->persist($groupeAmis);
            // $this->addReference('amis', $groupeAmis);
            //
            // $em->flush();

            // Perf mode: create additional groups
            if ($this->perfMode) {
                // TODO: Implement when GroupeAdaptor entity is created
                //
                // Planned perf groups:
                // for ($i = 1; $i <= 3; $i++) {
                //     $groupe = new GroupeAdaptor()
                //         ->setNom("Groupe Perf {$i}")
                //         ->setDescription("Groupe de test de performance {$i}");
                //     $em->persist($groupe);
                //     $this->addReference("groupe-perf-{$i}", $groupe);
                // }
                // $em->flush();

                $this->output->writeln(['  + Groupes perf: SCAFFOLD - en attente de l\'entité GroupeAdaptor.']);
            }
        }
        $this->output->writeln(['Groupes: SCAFFOLD - en attente de l\'entité GroupeAdaptor (Story 2.1).']);
    }
}
