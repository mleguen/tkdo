<?php

namespace App\Appli\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AppAbstractFixture extends AbstractFixture
{
    /** @var OutputInterface */
    protected $output;

    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;
        return $this;
    }
}