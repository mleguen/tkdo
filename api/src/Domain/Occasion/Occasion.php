<?php

declare(strict_types=1);

namespace App\Domain\Occasion;

use JsonSerializable;

class Occasion implements JsonSerializable
{
    private readonly string $occasionname;

    private readonly string $firstName;

    private readonly string $lastName;

    public function __construct(private readonly ?int $id, string $occasionname, string $firstName, string $lastName)
    {
        $this->occasionname = strtolower($occasionname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOccasionname(): string
    {
        return $this->occasionname;
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
            'username' => $this->occasionname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
