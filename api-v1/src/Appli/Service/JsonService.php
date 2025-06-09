<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Dom\Model\Exclusion;
use App\Dom\Model\Idee;
use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use Exception;

class JsonService
{
    public function __construct(private readonly AuthService $authService, private readonly DateService $dateService)
    {
    }

    private function getPayloadConnexion(Utilisateur $utilisateur): array
    {
        return [
            "token" => $this->authService->encode(AuthAdaptor::fromUtilisateur($utilisateur)),
            "utilisateur" => [
                "id" => $utilisateur->getId(),
                "nom" => $utilisateur->getNom(),
                "genre" => $utilisateur->getGenre(),
                "admin" => $utilisateur->getAdmin(),
            ]
        ];
    }

    private function getPayloadException(Exception $e): array
    {
        return [
            "message" => $e->getMessage(),
        ];
    }

    public function getPayloadExclusion(Exclusion $exclusion): array
    {
        return [
            "quiNeDoitPasRecevoir" => $this->getPayloadUtilisateur($exclusion->getQuiNeDoitPasRecevoir())
        ];
    }

    private function getPayloadIdee(Idee $idee): array
    {
        $json = [
            'id' => $idee->getId(),
            'description' => $idee->getDescription(),
            'auteur' => $this->getPayloadUtilisateur($idee->getAuteur()),
            'dateProposition' => $this->dateService->encodeDate($idee->getDateProposition()),
        ];

        if ($idee->hasDateSuppression()) {
            $json['dateSuppression'] = $this->dateService->encodeDate($idee->getDateSuppression());
        }

        return $json;
    }

    /**
     * @param Idee[] $idees
     */
    private function getPayloadListeIdees(Utilisateur $utilisateur, array $idees): array
    {
        return [
            "utilisateur" => $this->getPayloadUtilisateur($utilisateur),
            "idees" => array_map(
                fn(Idee $i) => $this->getPayloadIdee($i),
                // Obligatoire pour être encodé comme un tableau en JSON
                array_values($idees)
            ),
        ];
    }

    public function getPayloadOccasion(Occasion $occasion): array
    {
        return [
            'id' => $occasion->getId(),
            'date' => $this->dateService->encodeDate($occasion->getDate()),
            'titre' => $occasion->getTitre(),
        ];
    }

    /**
     * @param Resultat[] $resultats
     */
    public function getPayloadOccasionDetaillee(Occasion $occasion, array $resultats): array
    {
        return array_merge($this->getPayloadOccasion($occasion), [
            'participants' => array_map(
                fn(Utilisateur $u) => $this->getPayloadUtilisateur($u),
                $occasion->getParticipants()
            ),
            'resultats' => array_map(
                fn(Resultat $rt) => $this->getPayloadResultat($rt),
                $resultats
            ),
        ]);
    }

    public function getPayloadResultat(Resultat $resultat): array
    {
        return [
            "idQuiOffre" => $resultat->getQuiOffre()->getId(),
            "idQuiRecoit" => $resultat->getQuiRecoit()->getId(),
        ];
    }

    private function getPayloadUtilisateur(Utilisateur $utilisateur): array
    {
        return [
            'genre' => $utilisateur->getGenre(),
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
        ];
    }

    private function getPayloadUtilisateurComplet(Utilisateur $utilisateur): array
    {
        $data = array_merge($this->getPayloadUtilisateur($utilisateur), [
            'email' => $utilisateur->getEmail(),
            'admin' => $utilisateur->getAdmin(),
            'identifiant' => $utilisateur->getIdentifiant(),
            'prefNotifIdees' => $utilisateur->getPrefNotifIdees(),
        ]);
        ksort($data);
        return $data;
    }

    public function encode(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    }

    public function encodeConnexion(Utilisateur $utilisateur): string
    {
        return $this->encode($this->getPayloadConnexion($utilisateur));
    }

    public function encodeException(Exception $e): string
    {
        return $this->encode($this->getPayloadException($e));
    }

    public function encodeExclusion(Exclusion $exclusion): string
    {
        return $this->encode($this->getPayloadExclusion($exclusion));
    }

    public function encodeIdee(Idee $idee): string
    {
        return $this->encode($this->getPayloadIdee($idee));
    }

    public function encodeListeIdees(Utilisateur $utilisateur, array $idees): string
    {
        return $this->encode($this->getPayloadListeIdees($utilisateur, $idees));
    }

    /**
     * @param Exclusion[] $exclusions
     */
    public function encodeListeExclusions(array $exclusions): string
    {
        return $this->encode(array_map($this->getPayloadExclusion(...), $exclusions));
    }

    /**
     * @param Occasion[] $occasions
     */
    public function encodeListeOccasions(array $occasions): string
    {
        return $this->encode(array_map($this->getPayloadOccasion(...), $occasions));
    }

    /**
     * @param Utilisateur[] $utilisateurs
     */
    public function encodeListeUtilisateurs(array $utilisateurs): string
    {
        return $this->encode(array_map([$this, 'getPayloadUtilisateur'], $utilisateurs));
    }

    public function encodeOccasion(Occasion $occasion): string
    {
        return $this->encode($this->getPayloadOccasion($occasion));
    }

    /**
     * @param Resultat[] $resultats
     */
    public function encodeOccasionDetaillee(Occasion $occasion, array $resultats): string
    {
        return $this->encode($this->getPayloadOccasionDetaillee($occasion, $resultats));
    }

    public function encodeParticipant(Occasion $occasion, Utilisateur $participant): string
    {
        return $this->encodeUtilisateur($participant);
    }

    public function encodeResultat(Resultat $resultat): string
    {
        return $this->encode($this->getPayloadResultat($resultat));
    }

    public function encodeUtilisateur(Utilisateur $utilisateur): string
    {
        return $this->encode($this->getPayloadUtilisateur($utilisateur));
    }

    public function encodeUtilisateurComplet(Utilisateur $utilisateur): string
    {
        return $this->encode($this->getPayloadUtilisateurComplet($utilisateur));
    }
}
