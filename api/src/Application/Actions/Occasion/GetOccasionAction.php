<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use Psr\Http\Message\ResponseInterface as Response;

class GetOccasionAction extends Action
{
  protected function action(): Response
  {
    return $this->respondWithData(MockData::occasion);
  }
}
