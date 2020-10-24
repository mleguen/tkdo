<?php
declare(strict_types=1);

namespace App\Domain\Connexion;

use JsonSerializable;

class Connexion implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $connexionname;

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
     * @param string    $connexionname
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $connexionname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->connexionname = strtolower($connexionname);
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
    public function getConnexionname(): string
    {
        return $this->connexionname;
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
            'username' => $this->connexionname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
