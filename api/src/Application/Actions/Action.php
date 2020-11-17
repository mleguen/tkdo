<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;

abstract class Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @return Response
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    protected function getFormData()
    {
        $contentTypes = $this->request->getHeader('Content-type');
        switch (end($contentTypes)) {
            case 'application/json':
                $input = json_decode($this->request->getBody()->getContents(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
                }
            break;

            case 'application/x-www-form-urlencoded':
                parse_str($this->request->getBody()->getContents(), $input);
            break;

            default:
                $input = $this->request->getParsedBody();
        }
        return $input;
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param  array|object|null $data
     * @return Response
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT)."\n";
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }

    /**
     * Vérifie qu'un utilisateur s'est authentifié
     */
    protected function assertAuth()
    {
        if (is_null($this->request->getAttribute('idUtilisateurAuth'))) {
            throw new HttpUnauthorizedException($this->request);
        }
    }

    /**
     * Vérifie que l'utilisateur authentifié est bien celui attendu (ou est admin)
     */
    protected function assertUtilisateurAuthEst(int $idUtilisateurAttendu, $warning = null)
    {
        if (
            !$this->request->getAttribute('estAdmin') &&
            ($this->request->getAttribute('idUtilisateurAuth') !== $idUtilisateurAttendu)
        ) {
            if (!is_null($warning)) {
                $this->logger->warning($warning);
            }
            throw new HttpForbiddenException($this->request);
        }
    }

    /**
     * Vérifie que l'utilisateur authentifié est admin
     */
    protected function assertUtilisateurAuthEstAdmin($warning = null)
    {
        if (!$this->request->getAttribute('estAdmin')) {
            if (!is_null($warning)) {
                $this->logger->warning($warning);
            }
            throw new HttpForbiddenException($this->request);
        }
    }
}
