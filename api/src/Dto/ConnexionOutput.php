<?php

namespace App\Dto;

class ConnexionOutput
{
    public function __construct(
        public string $token,
        public string $utilisateurId,
        public string $nom,
        public string $prenom
    ) {}
}
