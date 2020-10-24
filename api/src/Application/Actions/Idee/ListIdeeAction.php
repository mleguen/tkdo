<?php
declare(strict_types=1);

namespace App\Application\Actions\Idee;

use Psr\Http\Message\ResponseInterface as Response;

class ListIdeeAction extends IdeeAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $idees = $this->ideeRepository->findAll();

        $this->logger->info("Idee list was viewed.");

        return $this->respondWithData($idees);
    }
}
