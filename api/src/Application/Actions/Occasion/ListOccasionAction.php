<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use Psr\Http\Message\ResponseInterface as Response;

class ListOccasionAction extends OccasionAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $occasions = $this->occasionRepository->findAll();

        $this->logger->info("Occasion list was viewed.");

        return $this->respondWithData($occasions);
    }
}
