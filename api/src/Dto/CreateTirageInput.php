<?php

namespace App\Dto;

class CreateTirageInput
{
    public function __construct(
        /** @var string[] */
        public array $participantIds = []
    ) {}
}
