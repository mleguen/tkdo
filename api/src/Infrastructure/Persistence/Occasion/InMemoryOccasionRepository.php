<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Utilisateur\Utilisateur;

class InMemoryOccasionRepository implements OccasionRepository
{
    /**
     * @var DoctrineOccasion[]
     */
    private $occasions;

    /**
     * @param DoctrineOccasion[] $occasions
     */
    public function __construct(array $occasions = [])
    {
        $this->occasions = $occasions;
    }

    /**
     * {@inheritdoc}
     */
    public function readLastByParticipant(int $idParticipant): Occasion
    {
        $occasions = array_filter(
            $this->occasions,
            function (Occasion $occasion) use ($idParticipant) {
                return in_array(
                    $idParticipant,
                    array_map(
                        function (Utilisateur $participant) {
                            return $participant->getId();
                        },
                        $occasion->getParticipants()
                    )
                );
            }
        );
        if (empty($occasions)) throw new OccasionNotFoundException();

        $lastId = max(array_keys($occasions));
        return clone $this->occasions[$lastId];
    }
}
