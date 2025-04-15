<?php

declare(strict_types=1);

namespace App\Domain\Idee;

use JsonSerializable;

class Idee implements JsonSerializable
{
    private ?int $id;

    private string $ideename;

    private string $firstName;

    private string $lastName;

    public function __construct(?int $id, string $ideename, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->ideename = strtolower($ideename);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdeename(): string
    {
        return $this->ideename;
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
            'username' => $this->ideename,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
