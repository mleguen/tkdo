<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\AuthCodeAdaptor;
use App\Dom\Model\AuthCode;
use App\Dom\Repository\AuthCodeRepository;
use DateTime;
use Doctrine\ORM\EntityManager;

class AuthCodeRepositoryAdaptor implements AuthCodeRepository
{
    protected EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function create(int $utilisateurId, int $expirySeconds = 60): array
    {
        // Generate cryptographically secure random code
        $codeClair = bin2hex(random_bytes(32));

        $expiresAt = new DateTime();
        $expiresAt->modify("+{$expirySeconds} seconds");

        $authCode = new AuthCodeAdaptor();
        $authCode->setCodeHash($codeClair)
            ->setUtilisateurId($utilisateurId)
            ->setExpiresAt($expiresAt);

        $this->em->persist($authCode);
        $this->em->flush();

        return [
            'code' => $codeClair,
            'authCode' => $authCode,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function read(int $id): ?AuthCode
    {
        /** @var AuthCode|null */
        return $this->em->getRepository(AuthCodeAdaptor::class)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function readValidByUtilisateur(int $utilisateurId): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
            ->from(AuthCodeAdaptor::class, 'c')
            ->where('c.utilisateurId = :utilisateurId')
            ->andWhere('c.expiresAt > :now')
            ->andWhere('c.usedAt IS NULL')
            ->setParameter('utilisateurId', $utilisateurId)
            ->setParameter('now', new DateTime());

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * Uses atomic UPDATE to prevent race conditions:
     * Only one concurrent request can successfully mark the code as used.
     */
    #[\Override]
    public function marqueUtilise(int $codeId): bool
    {
        $qb = $this->em->createQueryBuilder();
        $affected = $qb->update(AuthCodeAdaptor::class, 'c')
            ->set('c.usedAt', ':now')
            ->where('c.id = :id')
            ->andWhere('c.usedAt IS NULL')  // Only if not already used
            ->setParameter('now', new DateTime())
            ->setParameter('id', $codeId)
            ->getQuery()
            ->execute();

        return $affected === 1;  // True only if we marked it
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function purgeExpired(\DateTimeInterface $olderThan): int
    {
        $qb = $this->em->createQueryBuilder();
        return (int) $qb->delete(AuthCodeAdaptor::class, 'c')
            ->where('c.expiresAt < :threshold')
            ->setParameter('threshold', $olderThan)
            ->getQuery()
            ->execute();
    }
}
