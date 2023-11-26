<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\ResultatAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Bootstrap;
use App\Dom\Model\Genre;
use App\Dom\Model\Occasion;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Iterator;
use PHPUnit\Framework\TestCase;
use rpkamp\Mailhog\MailhogClient;
use rpkamp\Mailhog\Message\Contact;
use rpkamp\Mailhog\Message\Message;

class IntTestCase extends TestCase
{
    /**
     * L'EntityManager partagé par tous les tests pour créer et supprimer les jeux de test
     * 
     * @var EntityManager
     * */
    protected static $em;

    /** @var GuzzleClient */
    private $client;
    /** @var MailhogClient */
    private $mhclient;
    /** @var string */
    protected $token;

    public static function setUpBeforeClass(): void
    {
        if (!self::$em) {
            $bootstrap = new Bootstrap();
            $container = $bootstrap->initContainer();
            self::$em = $container->get(EntityManager::class);
        }
    }

    public function setUp(): void
    {
        $this->client = new GuzzleClient();
        $this->mhclient = new MailhogClient(new GuzzleAdapter($this->client), new GuzzleMessageFactory(), getenv('MAILHOG_BASE_URI'));
        $this->mhclient->purgeMessages();
    }

    public function tearDown(): void
    {
        foreach ([
            IdeeAdaptor::class,
            ResultatAdaptor::class,
            OccasionAdaptor::class,
            ExclusionAdaptor::class,
            UtilisateurAdaptor::class,
        ] as $class) {
            foreach (self::$em->getRepository($class)->findAll() as $entite) {
                self::$em->remove($entite);
            }
        }
        self::$em->flush();
    }

    /**
     * Asserts that an array has a specified subset.
     * 
     * Version simplifiée (pour l'utilisation qui en est faite ici)
     * de assertArraySubset supprimée dans phpunit 9.
     */
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        parent::assertEquals($subset, array_intersect_key($array, $subset));
    }

    protected static function assertMessageRecipientsContains(string $email, Message $message): void
    {
        self::assertTrue($message->recipients->contains(new Contact($email)));
    }

    protected function creeIdeeEnBase(array $options = []): IdeeAdaptor
    {
        $idee = $this->creeIdeeEnMemoire($options);
        self::$em->persist($idee);
        self::$em->flush();
        return $idee;
    }

    protected function creeIdeeEnMemoire(array $options = []): IdeeAdaptor
    {
        $idee = (new IdeeAdaptor())
            ->setUtilisateur($options['utilisateur'] ?? $this->creeUtilisateurEnBase('utilisateur'))
            ->setDescription($options['description'] ?? 'nouvelle idée')
            ->setAuteur($options['auteur'] ?? $this->creeUtilisateurEnBase('auteur'))
            ->setDateProposition($options['dateProposition'] ?? new DateTime());
        if (isset($options['dateSuppression'])) $idee->setDateSuppression($options['dateSuppression']);
        return $idee;
    }

    /**
     * @param UtilisateurAdaptor[] $participants
     */
    protected function creeOccasionEnBase(array $options = []): OccasionAdaptor
    {
        $occasion = $this->creeOccasionEnMemoire($options);
        self::$em->persist($occasion);
        self::$em->flush();
        return $occasion;
    }

    /**
     * @param UtilisateurAdaptor[] $participants
     */
    protected function creeOccasionEnMemoire(array $options = []): OccasionAdaptor
    {
        return (new OccasionAdaptor())
            ->setDate($options['date'] ?? new DateTime('tomorrow'))
            ->setParticipants($options['participants'] ?? [])
            ->setTitre($options['titre'] ?? 'demain');
    }

    protected function creeResultatEnBase(
        OccasionAdaptor $occasion,
        UtilisateurAdaptor $quiOffre,
        UtilisateurAdaptor $quiRecoit
    ): ResultatAdaptor
    {
        $resultat = $this->creeResultatEnMemoire($occasion, $quiOffre, $quiRecoit);
        self::$em->persist($resultat);
        self::$em->flush();

        return $resultat;
    }

    protected function creeResultatEnMemoire(
        OccasionAdaptor $occasion,
        UtilisateurAdaptor $quiOffre,
        UtilisateurAdaptor $quiRecoit
    ): ResultatAdaptor
    {
        return (new ResultatAdaptor())
            ->setOccasion($occasion)
            ->setQuiOffre($quiOffre)
            ->setQuiRecoit($quiRecoit);
    }

    protected function creeUtilisateurEnBase(string $identifiant, array $options = []): UtilisateurAdaptor
    {
        $mdp = $options['mdp'] ?? 'mdp' . $identifiant;
        $utilisateur = $this->creeUtilisateurEnMemoire($identifiant, $options)
            ->setMdpClair($mdp);
        self::$em->persist($utilisateur);
        self::$em->flush();

        return $utilisateur;
    }

    protected function creeUtilisateurEnMemoire(string $identifiant, array $options = []): UtilisateurAdaptor
    {
        return (new UtilisateurAdaptor())
            ->setEmail($options['email'] ?? $identifiant . '@localhost')
            ->setAdmin($options['admin'] ?? false)
            ->setGenre($options['genre'] ?? Genre::Masculin)
            ->setIdentifiant($identifiant)
            ->setNom($options['nom'] ?? $identifiant)
            ->setDateDerniereNotifPeriodique($options['dateDerniereNotifPeriodique'] ?? new DateTime())
            ->setPrefNotifIdees($options['prefNotifIdees'] ?? PrefNotifIdees::Aucune);
    }

    /**
     * @return Message[]
     */
    protected function depileDerniersEmailsRecus(): array
    {
        $emailsRecus = iterator_to_array($this->mhclient->findAllMessages());
        $this->mhclient->purgeMessages();
        return $emailsRecus;
    }

    protected function postConnexion(bool $curl, UtilisateurAdaptor $utilisateur = null): UtilisateurAdaptor
    {
        if (!$utilisateur) $utilisateur = $this->creeUtilisateurEnBase('connecte');
        $this->requestApi(
            $curl,
            'POST',
            '/connexion',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        $this->assertArraySubset([
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
                'admin' => $utilisateur->getAdmin(),
            ]
        ], $body ?: []);
        
        $this->assertArrayHasKey('token', $body);
        $this->token = $body['token'];

        return $utilisateur;
    }

    protected function requestApi(
        bool $curl,
        string $method,
        string $path,
        int &$statusCode = null,
        array &$body = null,
        $query = '',
        array $data = null,
        $showOutput = false
    ): void {
        if ($curl) {
            $uParam = $this->token ? "-u {$this->token}:" : '';
            $dParams = $data ? implode(' ', array_map(function($k, $v) {
                if (is_string($v)) $v = urlencode($v);
                return "-d $k=$v";
            }, array_keys($data), $data)) : '';

            $fullPath = getenv('TKDO_BASE_URI') . $path;
            if ($query) $fullPath .= "?$query";

            exec(
                "curl $uParam $dParams -s -w '\\nCURL_HTTP_CODE=%{http_code}\\n' -X $method '$fullPath'",
                $output
            );
            $output = array_filter($output, function(string $outputLine) use (&$statusCode) {
                if (preg_match('/^CURL_HTTP_CODE=(\d+)$/', $outputLine, $matches)) {
                    // curl peut renvoyer plusieurs status codes, mais seul le dernier nous intéresse
                    $statusCode = intval($matches[1]);
                    return false;
                } else {
                    return true;
                }
            });
            if ($showOutput) var_dump($output);
            $jsonBody = implode("\n", $output);
        }
        else {
            $headers = [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
            ];
            if ($this->token) $headers = array_merge(['Authorization' => "Bearer $this->token"], $headers);
    
            $response = $this->client->request(
                $method,
                getenv('TKDO_BASE_URI') . $path,
                [
                    'body' => is_null($data) ? '' : (count($data) ? json_encode($data) : '{}'),
                    'cookie' => true,
                    'headers' => $headers,
                    'http_errors' => false,
                    'query' => $query,
                ]
            );
            $statusCode = $response->getStatusCode();
            $jsonBody = (string) $response->getBody();
        }
        $body = $jsonBody ? json_decode($jsonBody, true) : null;
    }

    protected static function occasionAttendue(Occasion $occasion): array
    {
        return [
            'id' => $occasion->getId(),
            'date' => $occasion->getDate()->format(DateTimeInterface::W3C),
            'titre' => $occasion->getTitre(),
        ];
    }

    /** @param Resultat[] $resultats */
    protected static function occasionDetailleeAttendue(Occasion $occasion, array $resultats = []): array
    {
        return array_merge(self::occasionAttendue($occasion), [
            'participants' => array_map([self::class, 'utilisateurAttendu'], $occasion->getParticipants()),
            'resultats' => array_map([self::class, 'resultatAttendu'], $resultats),
        ]);
    }

    protected static function resultatAttendu(Resultat $resultat): array
    {
        return [
            'idQuiOffre' => $resultat->getQuiOffre()->getId(),
            'idQuiRecoit' => $resultat->getQuiRecoit()->getId(),
        ];
    }

    protected static function utilisateurAttendu(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'genre' => $utilisateur->getGenre(),
        ];
    }

    public function provideCurl(): Iterator
    {
        foreach([false, true] as $curl) {
            yield [$curl];
        }
    }
}