<?php

namespace App\Infrastructure\Persistence;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DoctrineAbstractFixture extends AbstractFixture
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