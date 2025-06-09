<?php

namespace App\Dto;

class AddParticipantsInput
{
    public function __construct(
        /** @var string[] */
        public array $utilisateurIds = []
    ) {}
}
