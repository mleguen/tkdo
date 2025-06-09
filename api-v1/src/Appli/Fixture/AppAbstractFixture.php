<?php

namespace App\Appli\Fixture;

use App\Bootstrap;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AppAbstractFixture extends AbstractFixture
{
    /** @var OutputInterface */
    protected $output;
    protected $devMode;

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
}