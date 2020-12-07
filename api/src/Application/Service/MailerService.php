<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Idee\Idee;
use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\Resultat;
use App\Domain\Utilisateur\Utilisateur;
use DateTimeInterface;
use Psr\Http\Message\ServerRequestInterface;

class MailerService
{
  const MODE_FILE = 'file';
  const MODE_MAIL = 'mail';

  const SIGNATURE = <<<EOS
Cordialement,
Votre administrateur Tkdo.
EOS;

  private $settings;

  public function __construct(array $settings = [])
  {
    $this->settings = array_merge([
      'mode' => self::MODE_MAIL,
      'signature' => self::SIGNATURE
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
    Utilisateur $destinataire,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $request,
      $destinataire,
      $mdp,
      'Création de votre compte',
      "Votre compte Tkdo (tirages cadeaux) vient d'être créé"
    );
  }

  public function envoieMailReinitialisationMdp(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $request,
      $destinataire,
      $mdp,
      'Réinitialisation de votre mot de passe',
      'Le mot de passe de votre compte Tkdo (tirages cadeaux) a été réinitialisé'
    );
  }

  private function envoieMailMdp(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    string $mdp,
    string $sujet,
    string $motif
  ): bool
  {
    return $this->mail(
      $request,
      $destinataire->getEmail(),
      $sujet,
      <<<EOS
Bonjour {$destinataire->getNom()},

$motif.

Pour accéder à l'application, connectez vous à {$this->getUri($request)}
avec les identifiants suivants :
- identifiant : {$destinataire->getIdentifiant()}
- mot de passe : $mdp

{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailAjoutParticipant(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    Occasion $occasion
  ): bool
  {
    return $this->mail(
      $request,
      $destinataire->getEmail(),
      "Participation au tirage cadeaux {$occasion->getTitre()}",
      <<<EOS
Bonjour {$destinataire->getNom()},

Vous participez désormais au tirage cadeaux {$occasion->getTitre()}.

Pour découvrir les noms des autres participants,
et commencer à proposer des idées de cadeaux,
rendez-vous sur {$this->getUri($request, "/occasion/{$occasion->getId()}")}

{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailTirageFait(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    Occasion $occasion
  ): bool
  {
    return $this->mail(
      $request,
      $destinataire->getEmail(),
      "Tirage au sort fait pour {$occasion->getTitre()}",
      <<<EOS
Bonjour {$destinataire->getNom()},

Le tirage au sort est fait pour '{$occasion->getTitre()}' !

Pour découvrir à qui vous aurez le plaisir de faire un cadeau,
rendez-vous sur {$this->getUri($request, "/occasion/{$occasion->getId()}")}

{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailCreationIdee(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    Idee $idee
  ): bool
  {
    return $this->envoieMailIdee(
      $request,
      $destinataire,
      $utilisateur,
      "Nouvelle idée de cadeau pour {$utilisateur->getNom()}",
      "Une nouvelle idée de cadeau a été proposée pour {$utilisateur->getNom()} :\n\n  > {$idee->getDescription()}"
    );
  }

  public function envoieMailSuppressionIdee(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    Idee $idee
  ): bool
  {
    return $this->envoieMailIdee(
      $request,
      $destinataire,
      $utilisateur,
      "Idée de cadeau supprimée pour {$utilisateur->getNom()}",
      "L'idée de cadeau pour {$utilisateur->getNom()} ci-dessous a été retirée de sa liste :\n\n  > {$idee->getDescription()}"
    );
  }

  private function envoieMailIdee(
    ServerRequestInterface $request,
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    string $sujet,
    string $motif
  ): bool
  {
    return $this->mail(
      $request,
      $destinataire->getEmail(),
      $sujet,
      <<<EOS
Bonjour {$destinataire->getNom()},

$motif

Pour consulter la liste d'idée de {$utilisateur->getNom()},
rendez-vous sur {$this->getUri($request, '/idee', "idUtilisateur={$utilisateur->getId()}")}

{$this->settings['signature']}
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
