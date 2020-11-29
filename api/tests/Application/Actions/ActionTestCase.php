<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use App\Application\Service\AuthService;
use App\Application\Service\MailerService;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\Genre;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Tests\TestCase;

abstract class ActionTestCase extends TestCase
{
    /** @var App */
    protected $app;
    /** @var ObjectProphecy */
    protected $ideeRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $occasionRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $resultatRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $utilisateurRepositoryProphecy;
    /** @var AuthService */
    protected $authService;
    /** @var DoctrineUtilisateur */
    protected $alice;
    /** @var DoctrineUtilisateur */
    protected $bob;
    /** @var DoctrineUtilisateur */
    protected $charlie;
    /** @var string */
    protected $mdpalice;
    /** @var string */
    protected $mdpbob;
    /** @var string */
    protected $mdpcharlie;
    /** @var string */
    protected $clePrivee;
    /** @var string */
    protected $clePublique;

    public function setUp(): void
    {
        $this->app = $this->getAppInstance();

        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->container->set(IdeeRepository::class, $this->ideeRepositoryProphecy->reveal());

        $this->occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $this->container->set(OccasionRepository::class, $this->occasionRepositoryProphecy->reveal());

        $this->resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $this->container->set(ResultatRepository::class, $this->resultatRepositoryProphecy->reveal());

        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $this->container->set(UtilisateurRepository::class, $this->utilisateurRepositoryProphecy->reveal());

        $this->mailerServiceProphecy = $this->prophesize(MailerService::class);
        $this->container->set(MailerService::class, $this->mailerServiceProphecy->reveal());

        $this->authService = $this->container->get(AuthService::class);

        $this->mdpalice = 'mdpalice';
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice')
            ->setEmail('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp(password_hash($this->mdpalice, PASSWORD_DEFAULT))
            ->setGenre(Genre::Feminin)
            ->setEstAdmin(true);
        $this->mdpbob = 'mdpbob';
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob')
            ->setEmail('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp(password_hash($this->mdpbob, PASSWORD_DEFAULT))
            ->setGenre(Genre::Masculin)
            ->setEstAdmin(false);
        $this->mdpcharlie = 'mdpcharlie';
        $this->charlie = (new DoctrineUtilisateur(3))
            ->setIdentifiant('charlie')
            ->setEmail('charlie@tkdo.org')
            ->setNom('Charlie')
            ->setMdp(password_hash($this->mdpcharlie, PASSWORD_DEFAULT))
            ->setGenre(Genre::Masculin)
            ->setEstAdmin(false);
    }

    protected function handleRequest(
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $query, $body);
        return $this->app->handle($request);
    }

    protected function handleRequestWithAuthHeader(
        string $authHeader,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest(
            $method,
            $path,
            $query,
            $body,
            ['HTTP_AUTHORIZATION' => $authHeader]
        );
        return $this->app->handle($request);
    }

    protected function handleAuthRequest(
        int $idUtilisateurAuth,
        bool $estAdmin,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        return $this->handleRequestWithAuthHeader(
            "Bearer " . $this->authService->encodeAuthToken($idUtilisateurAuth, $estAdmin),
            $method, $path, $query, $body
        );
    }
}
