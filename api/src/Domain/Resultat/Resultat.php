<?php
declare(strict_types=1);

namespace App\Domain\Resultat;

use JsonSerializable;

class Resultat implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $resultatname;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @param int|null  $id
     * @param string    $resultatname
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $resultatname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->resultatname = strtolower($resultatname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResultatname(): string
    {
        return $this->resultatname;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'username' => $this->resultatname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
