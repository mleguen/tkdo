<?php

namespace App\Appli\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AppAbstractFixture extends AbstractFixture
{
    /** @var OutputInterface */
    protected $output;
    protected $prod;

    public function __construct(OutputInterface $output, bool $prod)
    {
        $this->output = $output;
        $this->prod = $prod;
    }
}