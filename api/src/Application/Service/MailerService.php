<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\Resultat;
use App\Domain\Utilisateur\Utilisateur;
use DateTimeInterface;
use Psr\Http\Message\ServerRequestInterface;

class MailerService
{
  const MODE_FILE = 'file';
  const MODE_MAIL = 'mail';

  private $settings;

  public function __construct(array $settings = [])
  {
    $this->settings = array_merge([
      'mode' => self::MODE_MAIL,
    ], $settings);

    if ($this->settings['mode'] === self::MODE_FILE) {
      if (!isset($this->settings['path'])) {
        throw new \Exception("Le paramètre 'path' doit être défini si 'mode' vaut MODE_FILE");
      }
      if (!is_dir($this->settings['path'])) {
        mkdir($this->settings['path'], 0777, true);
        if (!is_dir($this->settings['path'])) {
          throw new \Exception("Impossible de créer le répertoire '{$this->settings['path']}'");
        }
      }
    }
  }

  public function envoieMailCreationUtilisateur(
    ServerRequestInterface $request,
    Utilisateur $utilisateur,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $request,
      $utilisateur,
      $mdp,
      'Création de votre compte',
      "Votre compte Tkdo (tirages cadeaux) vient d'être créé"
    );
  }

  public function envoieMailReinitialisationMdp(
    ServerRequestInterface $request,
    Utilisateur $utilisateur,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $request,
      $utilisateur,
      $mdp,
      'Réinitialisation de votre mot de passe',
      'Le mot de passe de votre compte Tkdo (tirages cadeaux) a été réinitialisé'
    );
  }

  private function envoieMailMdp(
    ServerRequestInterface $request,
    Utilisateur $utilisateur,
    string $mdp,
    string $sujet,
    string $motif
  ): bool
  {
    return $this->mail(
      $request,
      $utilisateur->getEmail(),
      $sujet,
      <<<EOS
Bonjour {$utilisateur->getNom()},

$motif.

Pour accéder à l'application, connectez vous à {$this->getUri($request)}
avec les identifiants suivants :
- identifiant : {$utilisateur->getIdentifiant()}
- mot de passe : $mdp

Cordialement,
Votre administrateur Tkdo.
EOS
    );
  }

  public function envoieMailAjoutParticipant(
    ServerRequestInterface $request,
    Utilisateur $utilisateur,
    Occasion $occasion
  ): bool
  {
    return $this->mail(
      $request,
      $utilisateur->getEmail(),
      "Participation au tirage cadeaux {$occasion->getTitre()}",
      <<<EOS
Bonjour {$utilisateur->getNom()},

Vous participez désormais au tirage cadeaux {$occasion->getTitre()}.

Pour découvrir les noms des autres participants,
et commencer à proposer des idées de cadeaux,
rendez-vous sur {$this->getUri($request, "/occasion/{$occasion->getId()}")}

Cordialement,
Votre administrateur Tkdo.
EOS
    );
  }

  public function envoieMailTirageFait(
    ServerRequestInterface $request,
    Utilisateur $utilisateur,
    Occasion $occasion
  ): bool
  {
    return $this->mail(
      $request,
      $utilisateur->getEmail(),
      "Tirage au sort fait pour {$occasion->getTitre()}",
      <<<EOS
Bonjour {$utilisateur->getNom()},

Le tirage au sort est fait pour '{$occasion->getTitre()}' !

Pour découvrir à qui vous aurez le plaisir de faire un cadeau,
rendez-vous sur {$this->getUri($request, "/occasion/{$occasion->getId()}")}

Cordialement,
Votre administrateur Tkdo.
EOS
    );
  }

  public function mail(ServerRequestInterface $request, string $to, string $subject, string $message) : bool
  {
    try {
      $additional_headers = [
        'From' => !empty($this->settings['from']) ? $this->settings['from'] : "Tkdo <noreply@{$this->getHost($request)}>"
      ];

      if ($this->settings['mode'] === self::MODE_FILE) {
        $date = time();

        $headers = array_merge($additional_headers, [
          'Date' => date(DateTimeInterface::RFC2822, $date),
          'To' => $to,
          'Subject' => $subject,
        ]);

        $email = implode("\r\n", array_map(function ($key, $value) {
          return "$key: $value";
        }, array_keys($headers), $headers)) . "\r\n\r\n$message";

        $path = sprintf('%s%s%s-%s.eml', $this->settings['path'], DIRECTORY_SEPARATOR, date('YmdHis', $date), sha1($email));

        $file = fopen($path, 'w');
        if (!$file) throw new \Exception("Impossible d'ouvrir le fichier '$path' en écriture");

        fwrite($file, $email);
        fclose($file);
        return true;
      }

      return mail($to, $subject, $message, $additional_headers);
    }
    catch (\Exception $e) {
      return false;
    }
  }

  private function getUri(ServerRequestInterface $request, string $path = '', string $query = '')
  {
    return $request->getUri()->withUserInfo('')->withPath($path)->withQuery($query)->withFragment('');
  }

  private function getHost(ServerRequestInterface $request)
  {
    return $request->getUri()->getHost();
  }
}
