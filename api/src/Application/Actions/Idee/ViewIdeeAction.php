<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use Psr\Http\Message\ResponseInterface as Response;

class ViewIdeeAction extends IdeeAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $ideeId = (int) $this->resolveArg('id');
        $idee = $this->ideeRepository->findIdeeOfId($ideeId);

        $this->logger->info("Idee of id `${ideeId}` was viewed.");

        return $this->respondWithData($idee);
    }
}
