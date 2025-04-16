<?php

declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Exception\EmailInvalideException;
use App\Dom\Exception\GenreInvalideException;
use App\Dom\Exception\ModificationMdpInterditeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\PrefNotifIdeesInvalideException;
use App\Dom\Model\Auth;
use App\Dom\Model\Genre;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Plugin\PasswordPlugin;
use App\Dom\Repository\UtilisateurRepository;
use DateTime;

class UtilisateurPort
{
    public function __construct(private readonly MailPlugin $mailPlugin, private readonly PasswordPlugin $passwordPlugin, private readonly UtilisateurRepository $utilisateurRepository)
    {
    }

    /**
     * @throws PasAdminException
     * @throws IdentifiantDejaUtiliseException
     * @throws EmailInvalideException
     * @throws PrefNotifIdeesInvalideException
     * @throws GenreInvalideException
     */
    public function creeUtilisateur(
        Auth $auth,
        string $identifiant,
        string $email,
        string $nom,
        string $genre,
        ?bool $admin = null,
        ?string $prefNotifIdees = null
    ): Utilisateur
    {
        if (!$auth->estAdmin()) throw new PasAdminException();
        self::assertEstEmail($email);
        if (!in_array($genre, Genre::Tous)) throw new GenreInvalideException();
        if ($prefNotifIdees && !in_array($prefNotifIdees, PrefNotifIdees::Toutes)) throw new PrefNotifIdeesInvalideException();

        $mdp = $this->passwordPlugin->randomPassword();
        $utilisateur = $this->utilisateurRepository->create(
            $identifiant,
            $email,
            $mdp,
            $nom,
            $genre,
            $admin ?: false,
            $prefNotifIdees ?: PrefNotifIdees::Aucune,
            // Empêche les notifications des événéments antérieurs à la création de l'utilisateur d'être envoyées
            new DateTime()
        );

        $this->mailPlugin->envoieMailMdpCreation($utilisateur, $mdp);

        return $utilisateur;
    }

    /**
     * @throws PasUtilisateurNiAdminException
     */
    public function getUtilisateur(Auth $auth, Utilisateur $utilisateur): Utilisateur
    {
        if (!$auth->estUtilisateur($utilisateur) && !$auth->estAdmin()) throw new PasUtilisateurNiAdminException();
        return $utilisateur;
    }

    /**
     * @return Utilisateur[]
     * @throws PasAdminException
     */
    public function listeUtilisateurs(Auth $auth): array
    {
        if (!$auth->estAdmin()) throw new PasAdminException();
        return $this->utilisateurRepository->readAll();
    }

    /**
     * @throws PasUtilisateurNiAdminException
     * @throws ModificationMdpInterditeException
     * @throws PasAdminException
     * @throws PrefNotifIdeesInvalideException
     * @throws EmailInvalideException
     */
    public function modifieUtilisateur(
        Auth $auth,
        Utilisateur $utilisateur,
        array $modifications
    ): Utilisateur
    {
        if (!$auth->estUtilisateur($utilisateur) && !$auth->estAdmin()) throw new PasUtilisateurNiAdminException();

        if (isset($modifications['identifiant'])) $utilisateur->setIdentifiant($modifications['identifiant']);
        if (isset($modifications['email'])) {
            self::assertEstEmail($modifications['email']);
            $utilisateur->setEmail($modifications['email']);
        }
        if (isset($modifications['nom'])) $utilisateur->setNom($modifications['nom']);
        if (isset($modifications['genre'])) {
            if (!in_array($modifications['genre'], Genre::Tous)) throw new GenreInvalideException();
            $utilisateur->setGenre($modifications['genre']);
        }


        if (isset($modifications['mdp'])) {
            if (!$auth->estUtilisateur($utilisateur)) throw new ModificationMdpInterditeException();
            $utilisateur->setMdpClair($modifications['mdp']);
        }

        if (isset($modifications['admin']) && ($modifications['admin'] !== $utilisateur->getAdmin())) {
            if (!$auth->estAdmin()) throw new PasAdminException();
            $utilisateur->setAdmin($modifications['admin']);
        }

        if (isset($modifications['prefNotifIdees'])) {
            if (!in_array($modifications['prefNotifIdees'], PrefNotifIdees::Toutes)) throw new PrefNotifIdeesInvalideException();
            // Si on passe à une notification périodique, on réinitialise la date de dernière notification
            if (
                in_array($modifications['prefNotifIdees'], PrefNotifIdees::Periodiques) &&
                !in_array($utilisateur->getPrefNotifIdees(), PrefNotifIdees::Periodiques)
            ) {
                $utilisateur->setDateDerniereNotifPeriodique(new DateTime());
            }
            $utilisateur->setPrefNotifIdees($modifications['prefNotifIdees']);
        }

        return $this->utilisateurRepository->update($utilisateur);
    }

    /**
     * @throws PasAdminException
     */
    public function reinitMdpUtilisateur(
        Auth $auth,
        Utilisateur $utilisateur
    ): Utilisateur
    {
        if (!$auth->estAdmin()) throw new PasAdminException();

        $mdp = $this->passwordPlugin->randomPassword();
        $utilisateur->setMdpClair($mdp);
        $utilisateur = $this->utilisateurRepository->update($utilisateur);

        $this->mailPlugin->envoieMailMdpReinitialisation($utilisateur, $mdp);

        return $utilisateur;
    }

    /**
     * @throws EmailInvalideException
     */
    private static function assertEstEmail($email)
    {
        // Le .fr est un hack pour autoriser des noms de domaine sans point volontairement refusés par PHP
        // (cf. https://www.php.net/manual/fr/filter.filters.validate.php)
        if (!filter_var($email . '.fr', FILTER_VALIDATE_EMAIL)) throw new EmailInvalideException($email);
    }
}
