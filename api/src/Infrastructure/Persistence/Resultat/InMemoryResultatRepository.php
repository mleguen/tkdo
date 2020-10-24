<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Resultat;

use App\Domain\Resultat\Resultat;
use App\Domain\Resultat\ResultatNotFoundException;
use App\Domain\Resultat\ResultatRepository;

class InMemoryResultatRepository implements ResultatRepository
{
    /**
     * @var Resultat[]
     */
    private $resultats;

    /**
     * InMemoryResultatRepository constructor.
     *
     * @param array|null $resultats
     */
    public function __construct(array $resultats = null)
    {
        $this->resultats = $resultats ?? [
            1 => new Resultat(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Resultat(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Resultat(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Resultat(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Resultat(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->resultats);
    }

    /**
     * {@inheritdoc}
     */
    public function findResultatOfId(int $id): Resultat
    {
        if (!isset($this->resultats[$id])) {
            throw new ResultatNotFoundException();
        }

        return $this->resultats[$id];
    }
}
