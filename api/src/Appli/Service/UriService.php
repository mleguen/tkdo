<?php

declare(strict_types=1);

namespace App\Appli\Service;

use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\UriFactory;

class UriService
{
  /** @var UriInterface|null */
  private ?UriInterface $baseUri = null;

  /**
   * Set the base URI from an HTTP request (called by UriMiddleware).
   * Derives scheme://host[:port] from the request, respecting X-Forwarded-Proto.
   */
  public function setBaseUriFromRequest(UriInterface $requestUri, string $forwardedProto = ''): void
  {
    $scheme = $forwardedProto !== '' ? $forwardedProto : ($requestUri->getScheme() ?: 'http');
    $this->baseUri = $requestUri
        ->withScheme($scheme)
        ->withUserInfo('')
        ->withPath('')
        ->withQuery('')
        ->withFragment('');
  }

  public function getHost(): string
  {
    return $this->getBaseUri()->getHost();
  }

  public function getUri(string $path = '', string $query = ''): UriInterface
  {
    $baseUri = $this->getBaseUri();
    return $baseUri->withPath($baseUri->getPath() . $path)->withQuery($query);
  }

  /**
   * Lazy-load base URI: uses request-derived URI if set (HTTP context),
   * otherwise falls back to TKDO_BASE_URI env var (CLI context).
   */
  private function getBaseUri(): UriInterface
  {
    if ($this->baseUri === null) {
      $envUri = getenv('TKDO_BASE_URI') ?: 'http://localhost:4200';
      $this->baseUri = (new UriFactory())->createUri($envUri);
    }
    return $this->baseUri;
  }
}
