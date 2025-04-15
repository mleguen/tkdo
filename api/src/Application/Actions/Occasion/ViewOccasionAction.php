<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use Psr\Http\Message\ResponseInterface as Response;

class ViewOccasionAction extends OccasionAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $occasionId = (int) $this->resolveArg('id');
        $occasion = $this->occasionRepository->findOccasionOfId($occasionId);

        $this->logger->info("Occasion of id `${occasionId}` was viewed.");

        return $this->respondWithData($occasion);
    }
}
