<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use JsonSerializable;

class Idee implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $ideename;

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
     * @param string    $ideename
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $ideename, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->ideename = strtolower($ideename);
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
    public function getIdeename(): string
    {
        return $this->ideename;
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
            'username' => $this->ideename,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
