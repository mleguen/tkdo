<?php
declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\ModelAdaptor\AuthAdaptor;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class RouteService
{
    public function getArg(ServerRequestInterface $request, array $args, string $name)
    {
        if (!isset($args[$name])) {
            throw new HttpBadRequestException($request, "Argument `{$name}` manquant.");
        }

        return $args[$name];
    }

    public function getAuth(ServerRequestInterface $request): AuthAdaptor
    {
        if ($authErr = $request->getAttribute('authErr')) throw new HttpUnauthorizedException($request, $authErr->getMessage());
        return $request->getAttribute('auth');
    }

    public function getIntArg(ServerRequestInterface $request, array $args, string $name): int
    {
        return intval($this->getArg($request, $args, $name));
    }

    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    public function getParsedRequestBody(ServerRequestInterface $request, $champsObligatoires = [])
    {
        $contentTypes = $request->getHeader('Content-type');
        switch (end($contentTypes)) {
            case 'application/json':
                $input = json_decode($request->getBody()->getContents(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new HttpBadRequestException($request, 'Erreur de syntaxe JSON');
                }
            break;

            case 'application/x-www-form-urlencoded':
                parse_str($request->getBody()->getContents(), $input);
            break;

            default:
                $input = $request->getParsedBody();
        }

        if (is_null($input)) $input = [];

        foreach($champsObligatoires as $champObligatoire) {
            if (!isset($input[$champObligatoire])) throw new HttpBadRequestException($request, "champ '$champObligatoire' manquant");
        }

        return $input;
    }

    public function getResponseWithJsonBody(ResponseInterface $response, string $jsonBody): ResponseInterface
    {
        $response->getBody()->write($jsonBody);
        return $response = $response->withHeader('Content-Type', 'application/json');
    }
}
