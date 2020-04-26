<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetOccasionAction extends Action
{
  /**
   * @var MockData
   */
  protected $mock;

  /**
   * @param LoggerInterface $logger
   * @param MockData  $mock
   */
  public function __construct(LoggerInterface $logger, MockData $mock)
  {
    parent::__construct($logger);
    $this->mock = $mock;
  }

  protected function action(): Response
  {
    return $this->respondWithData($this->mock->getOccasion());
  }
}
