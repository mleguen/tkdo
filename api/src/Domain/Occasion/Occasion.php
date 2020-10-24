<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

use JsonSerializable;

class Occasion implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $occasionname;

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
     * @param string    $occasionname
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $occasionname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->occasionname = strtolower($occasionname);
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
    public function getOccasionname(): string
    {
        return $this->occasionname;
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
            'username' => $this->occasionname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
