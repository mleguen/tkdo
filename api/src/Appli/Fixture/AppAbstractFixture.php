<?php

namespace App\Appli\Fixture;

use App\Bootstrap;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base fixture class for application data fixtures.
 *
 * Supports two modes:
 * - devMode: Creates development test data (users, occasions, ideas)
 * - perfMode: Creates additional data for performance testing baselines
 *
 * When perfMode is enabled (via ./console fixtures --perf), fixtures create:
 * - 6 additional users (perf1-perf6) for 11 total participants
 * - 1 occasion "Occasion Perf Test" with 11 participants
 * - 22 additional ideas for user 'bob' (24 total)
 *
 * This ensures the performance baseline tests meet their data requirements:
 * - Occasion with 10+ participants
 * - User with 20+ ideas
 */
abstract class AppAbstractFixture extends AbstractFixture
{
    protected OutputInterface $output;
    protected bool $devMode;

    /**
     * When true, creates additional test data for performance baseline capture.
     * Enable via: ./console fixtures --perf
     */
    protected bool $perfMode = false;

    public function __construct(
        Bootstrap $bootstrap
    ) {
        $this->devMode = $bootstrap->devMode;
    }

    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Enable/disable performance testing mode.
     *
     * When enabled, fixtures create additional data to meet performance baseline requirements:
     * - UtilisateurFixture: 6 extra users (perf1-perf6)
     * - OccasionFixture: 1 occasion with 11 participants
     * - IdeeFixture: 22 extra ideas for bob
     */
    public function setPerfMode(bool $perfMode): self
    {
        $this->perfMode = $perfMode;
        return $this;
    }
}