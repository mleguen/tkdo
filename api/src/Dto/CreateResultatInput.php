<?php

namespace App\Dto;

class CreateResultatInput
{
    public function __construct(
        public string $donneurId,
        public string $receveurId,
        public ?string $ideeId = null
    ) {}
}
