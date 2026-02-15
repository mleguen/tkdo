<?php

declare(strict_types=1);

namespace App\Appli\Fixture;

use App\Bootstrap;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture for creating test Groupe entities
 *
 * DEFERRED: GroupeAdaptor entity exists (created in Story 2.1), but fixture
 * implementation is intentionally postponed until API endpoints require it (Story 2.3+).
 * No fixtures needed for database-layer-only stories.
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
            // DEFERRED: Fixture implementation postponed to Story 2.3+ (when API endpoints need it)
            //
            // Planned groups:
            // - 'famille': Groupe with alice, bob, charlie as members
            // - 'amis': Groupe with bob, david, eve as members
            //
            // Example implementation:
            // $groupeFamille = new GroupeAdaptor();
            // $groupeFamille->setNom('Famille')
            //     ->setArchive(false)
            //     ->setDateCreation(new DateTime());
            // $em->persist($groupeFamille);
            // $this->addReference('famille', $groupeFamille);
            //
            // // Add members via AppartenanceAdaptor
            // $appAlice = new AppartenanceAdaptor($groupeFamille, $this->getReference('alice', UtilisateurAdaptor::class));
            // $appAlice->setEstAdmin(true)->setDateAjout(new DateTime());
            // $em->persist($appAlice);
            //
            // $appBob = new AppartenanceAdaptor($groupeFamille, $this->getReference('bob', UtilisateurAdaptor::class));
            // $appBob->setEstAdmin(false)->setDateAjout(new DateTime());
            // $em->persist($appBob);
            //
            // $groupeAmis = new GroupeAdaptor();
            // $groupeAmis->setNom('Amis')
            //     ->setArchive(false)
            //     ->setDateCreation(new DateTime());
            // $em->persist($groupeAmis);
            // $this->addReference('amis', $groupeAmis);
            //
            // $em->flush();

            // Perf mode: create additional groups
            if ($this->perfMode) {
                // DEFERRED: Perf fixtures postponed to Story 2.3+ (when API endpoints need it)
                //
                // Planned perf groups:
                // for ($i = 1; $i <= 3; $i++) {
                //     $groupe = new GroupeAdaptor();
                //     $groupe->setNom("Groupe Perf {$i}")
                //         ->setArchive(false)
                //         ->setDateCreation(new DateTime());
                //     $em->persist($groupe);
                //     $this->addReference("groupe-perf-{$i}", $groupe);
                // }
                // $em->flush();

                $this->output->writeln(['  + Groupes perf: Fixture implementation deferred to Story 2.3+.']);
            }
        }
        $this->output->writeln(['Groupes: Fixture implementation deferred to Story 2.3+ (entity exists, no fixtures needed yet).']);
    }
}
