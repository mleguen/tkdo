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
  private $settings;

  public function __construct(UriSettings $settings)
  {
    $this->settings = $settings;
    $this->baseUri = (new UriFactory())->createUri($this->settings->baseUri);
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
