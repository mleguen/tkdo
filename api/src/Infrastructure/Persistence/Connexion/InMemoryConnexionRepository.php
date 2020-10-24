<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Connexion;

use App\Domain\Connexion\Connexion;
use App\Domain\Connexion\ConnexionNotFoundException;
use App\Domain\Connexion\ConnexionRepository;

class InMemoryConnexionRepository implements ConnexionRepository
{
    /**
     * @var Connexion[]
     */
    private $connexions;

    /**
     * InMemoryConnexionRepository constructor.
     *
     * @param array|null $connexions
     */
    public function __construct(array $connexions = null)
    {
        $this->connexions = $connexions ?? [
            1 => new Connexion(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Connexion(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Connexion(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Connexion(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Connexion(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->connexions);
    }

    /**
     * {@inheritdoc}
     */
    public function findConnexionOfId(int $id): Connexion
    {
        if (!isset($this->connexions[$id])) {
            throw new ConnexionNotFoundException();
        }

        return $this->connexions[$id];
    }
}
