<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use JsonSerializable;

class Utilisateur implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $utilisateurname;

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
     * @param string    $utilisateurname
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $utilisateurname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->utilisateurname = strtolower($utilisateurname);
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
    public function getUtilisateurname(): string
    {
        return $this->utilisateurname;
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
            'username' => $this->utilisateurname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
