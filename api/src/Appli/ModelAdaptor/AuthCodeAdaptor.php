<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\AuthCode;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(
 *     name="tkdo_auth_code",
 *     indexes={
 *         @Index(name="idx_expires_at", columns={"expires_at"}),
 *         @Index(name="idx_utilisateur_id", columns={"utilisateur_id"})
 *     }
 * )
 */
class AuthCodeAdaptor implements AuthCode
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @Column(name="code_hash")
     */
    private string $codeHash;

    /**
     * @Column(name="utilisateur_id", type="integer")
     */
    private int $utilisateurId;

    /**
     * @Column(name="expires_at", type="datetime")
     */
    private DateTime $expiresAt;

    /**
     * @Column(name="used_at", type="datetime", nullable=true)
     */
    private ?DateTime $usedAt = null;

    /**
     * @Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[\Override]
    public function getUtilisateurId(): int
    {
        return $this->utilisateurId;
    }

    #[\Override]
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    #[\Override]
    public function getUsedAt(): ?DateTime
    {
        return $this->usedAt;
    }

    #[\Override]
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[\Override]
    public function estExpire(): bool
    {
        return $this->expiresAt < new DateTime();
    }

    #[\Override]
    public function estUtilise(): bool
    {
        return $this->usedAt !== null;
    }

    #[\Override]
    public function verifieCode(string $codeClair): bool
    {
        return password_verify($codeClair, $this->codeHash);
    }

    public function setCodeHash(string $codeClair): self
    {
        $this->codeHash = password_hash($codeClair, PASSWORD_DEFAULT);
        return $this;
    }

    public function setUtilisateurId(int $utilisateurId): self
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function setExpiresAt(DateTime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function setUsedAt(?DateTime $usedAt): self
    {
        $this->usedAt = $usedAt;
        return $this;
    }
}
