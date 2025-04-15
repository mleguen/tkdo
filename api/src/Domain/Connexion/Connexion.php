<?php

declare(strict_types=1);

namespace App\Domain\Connexion;

use JsonSerializable;

class Connexion implements JsonSerializable
{
    private ?int $id;

    private string $connexionname;

    private string $firstName;

    private string $lastName;

    public function __construct(?int $id, string $connexionname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->connexionname = strtolower($connexionname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConnexionname(): string
    {
        return $this->connexionname;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->connexionname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
