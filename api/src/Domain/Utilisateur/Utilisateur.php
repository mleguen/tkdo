<?php

declare(strict_types=1);

namespace App\Domain\Utilisateur;

use JsonSerializable;

class Utilisateur implements JsonSerializable
{
    private readonly string $utilisateurname;

    private readonly string $firstName;

    private readonly string $lastName;

    public function __construct(private readonly ?int $id, string $utilisateurname, string $firstName, string $lastName)
    {
        $this->utilisateurname = strtolower($utilisateurname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateurname(): string
    {
        return $this->utilisateurname;
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
            'username' => $this->utilisateurname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
