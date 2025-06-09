<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurReinitMdpProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // The standard Utilisateur provider provides the entity in $data
        if (!$data instanceof Utilisateur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $utilisateur = $data;

        // Generate new temporary password
        $newPassword = $this->generateRandomPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $newPassword);

        $utilisateur->setMotDePasse($hashedPassword);
        $utilisateur->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        // Send email with new password
        $email = (new Email())
            ->from('noreply@yourapp.com')
            ->to($utilisateur->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->text("Votre nouveau mot de passe est : {$newPassword}");

        $this->mailer->send($email);

        return [
            'message' => 'Nouveau mot de passe envoyé par email',
            'email' => $utilisateur->getEmail()
        ];
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $password;
    }
}
