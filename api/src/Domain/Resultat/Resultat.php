<?php

declare(strict_types=1);

namespace App\Domain\Resultat;

use JsonSerializable;

class Resultat implements JsonSerializable
{
    private ?int $id;

    private string $resultatname;

    private string $firstName;

    private string $lastName;

    public function __construct(?int $id, string $resultatname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->resultatname = strtolower($resultatname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResultatname(): string
    {
        return $this->resultatname;
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
            'username' => $this->resultatname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
