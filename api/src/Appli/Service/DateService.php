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

  /**
   * @return DateTime|false
   */
  function decodeDate(string $date)
  {
    return new DateTime($date);
  }
}
