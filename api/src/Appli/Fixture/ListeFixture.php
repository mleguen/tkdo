<?php

declare(strict_types=1);

namespace App\Appli\Fixture;

use App\Bootstrap;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture for creating test Liste (visibility) entities
 *
 * SCAFFOLD: This fixture is a placeholder for v2 Liste entity support.
 * The actual ListeAdaptor entity will be created after GroupeAdaptor.
 * Update this fixture when the entity is implemented.
 *
 * Liste controls which groups can see a user's idea list.
 *
 * Planned data:
 * - DevMode: Visibility assignments for users to groups
 * - PerfMode: Additional visibility data for performance testing
 */
class ListeFixture extends AppAbstractFixture
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
            // TODO: Implement when ListeAdaptor and GroupeAdaptor entities are created
            //
            // Planned visibility assignments:
            // - Alice's list visible to 'famille'
            // - Bob's list visible to both 'famille' and 'amis'
            // - Charlie's list visible to 'famille'
            //
            // Example implementation:
            // $liste = new ListeAdaptor()
            //     ->setUtilisateur($this->getReference('alice', UtilisateurAdaptor::class))
            //     ->setGroupe($this->getReference('famille', GroupeAdaptor::class))
            //     ->setVisible(true);
            // $em->persist($liste);
            //
            // $em->flush();

            // Perf mode: create additional visibility assignments
            if ($this->perfMode) {
                // TODO: Implement when entities are created
                //
                // Planned perf data:
                // Assign all perf users to perf groups with varying visibility

                $this->output->writeln(['  + Listes perf: SCAFFOLD - en attente des entités.']);
            }
        }
        $this->output->writeln(['Listes: SCAFFOLD - en attente des entités ListeAdaptor et GroupeAdaptor.']);
    }
}
