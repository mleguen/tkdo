<?php

declare(strict_types=1);

namespace App\Appli\Service;

use DateTime;
use DateTimeInterface;

class DateService
{
  function encodeDate(DateTime $date): string
  {
    return $date->format(DateTimeInterface::W3C);
  }

  function decodeDate(string $date): DateTime
  {
    return new DateTime($date);
  }
}
