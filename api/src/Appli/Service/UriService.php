<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Settings\UriSettings;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\UriFactory;

class UriService
{
  /** @var UriInterface */
  private $baseUri;

  public function __construct(private readonly UriSettings $settings)
  {
    $this->baseUri = new UriFactory()->createUri($this->settings->baseUri);
  }
  
  public function getHost()
  {
    return $this->baseUri->getHost();
  }
  
  public function getUri(string $path = '', string $query = '')
  {
    return $this->baseUri->withPath($this->baseUri->getPath() . $path)->withQuery($query);
  }
}
