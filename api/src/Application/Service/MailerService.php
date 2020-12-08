<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Idee\Idee;
use App\Domain\Occasion\Occasion;
use App\Domain\Utilisateur\Utilisateur;
use DateTimeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

class MailerService
{
  const MODE_FILE = 'file';
  const MODE_MAIL = 'mail';

  const SIGNATURE = <<<EOS
Cordialement,
Votre administrateur Tkdo.
EOS;

  /** @var UriInterface */
  private $baseUri;
  private $settings;

  public function __construct(ContainerInterface $c)
  {
    $this->settings = array_merge([
      'mode' => self::MODE_MAIL,
      'signature' => self::SIGNATURE
    ], $c->get('settings')['mailer']);
    $this->baseUri = $this->settings['baseUri'];

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
  
  private function envoieMail(string $to, string $subject, string $message) : bool
  {
    try {
      $additional_headers = [
        'From' => !empty($this->settings['from']) ? $this->settings['from'] : "Tkdo <noreply@{$this->baseUri->getHost()}>"
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

  public function envoieMailAjoutParticipant(
    Utilisateur $destinataire,
    Occasion $occasion
  ): bool
  {
    return $this->envoieMail(
      $destinataire->getEmail(),
      "Participation au tirage cadeaux {$occasion->getTitre()}",
      <<<EOS
Bonjour {$destinataire->getNom()},

Vous participez désormais au tirage cadeaux {$occasion->getTitre()}.

Pour découvrir les noms des autres participants,
et commencer à proposer des idées de cadeaux,
rendez-vous sur {$this->getUri("/occasion/{$occasion->getId()}")}

{$this->settings['signature']}
EOS
    );
  }

  private function envoieMailIdee(
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    string $sujet,
    string $motif
  ): bool
  {
    return $this->envoieMail(
      $destinataire->getEmail(),
      $sujet,
      <<<EOS
Bonjour {$destinataire->getNom()},

$motif

Pour consulter la liste d'idée de {$utilisateur->getNom()},
rendez-vous sur {$this->getUri('/idee', "idUtilisateur={$utilisateur->getId()}")}

{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailIdeeCreation(
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    Idee $idee
  ): bool
  {
    return $this->envoieMailIdee(
      $destinataire,
      $utilisateur,
      "Nouvelle idée de cadeau pour {$utilisateur->getNom()}",
      "Une nouvelle idée de cadeau a été proposée pour {$utilisateur->getNom()} :\n\n  > {$idee->getDescription()}"
    );
  }

  public function envoieMailIdeeSuppression(
    Utilisateur $destinataire,
    Utilisateur $utilisateur,
    Idee $idee
  ): bool
  {
    return $this->envoieMailIdee(
      $destinataire,
      $utilisateur,
      "Idée de cadeau supprimée pour {$utilisateur->getNom()}",
      "L'idée de cadeau pour {$utilisateur->getNom()} ci-dessous a été retirée de sa liste :\n\n  > {$idee->getDescription()}"
    );
  }

  private function envoieMailMdp(
    Utilisateur $destinataire,
    string $mdp,
    string $sujet,
    string $motif
  ): bool
  {
    return $this->envoieMail(
      $destinataire->getEmail(),
      $sujet,
      <<<EOS
Bonjour {$destinataire->getNom()},

$motif.

Pour accéder à l'application, connectez vous à {$this->getUri()}
avec les identifiants suivants :
- identifiant : {$destinataire->getIdentifiant()}
- mot de passe : $mdp

{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailMdpCreation(
    Utilisateur $destinataire,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $destinataire,
      $mdp,
      'Création de votre compte',
      "Votre compte Tkdo (tirages cadeaux) vient d'être créé"
    );
  }

  public function envoieMailMdpReinitialisation(
    Utilisateur $destinataire,
    string $mdp
  ): bool
  {
    return $this->envoieMailMdp(
      $destinataire,
      $mdp,
      'Réinitialisation de votre mot de passe',
      'Le mot de passe de votre compte Tkdo (tirages cadeaux) a été réinitialisé'
    );
  }

  /**
   * @param Idee[] $idees
   */
  public function envoieMailNotificationPeriodique(
    Utilisateur $destinataire,
    array $idees
  ): bool {
    if (count($idees) === 0) return true;

    $ideesUtilisateurs = [];
    foreach($idees as $idee) {
      $nomUtilisateur = $idee->getUtilisateur()->getNom();
      if (!isset($ideesUtilisateurs[$nomUtilisateur])) {
        $ideesUtilisateurs[$nomUtilisateur] = new stdClass();
        $ideesUtilisateurs[$nomUtilisateur]->id = $idee->getUtilisateur()->getId();
        $ideesUtilisateurs[$nomUtilisateur]->creations = [];
        $ideesUtilisateurs[$nomUtilisateur]->suppressions = [];
      }
      if ($idee->hasDateSuppression()) {
        $ideesUtilisateurs[$nomUtilisateur]->suppressions[] = $idee;
      } else {
        $ideesUtilisateurs[$nomUtilisateur]->creations[] = $idee;
      }
    }
    ksort($ideesUtilisateurs);

    $contenu = "";
    foreach($ideesUtilisateurs as $nomUtilisateur => $ideesUtilisateur) {

      if (count($ideesUtilisateur->creations) > 0) {
        if (count($ideesUtilisateur->creations) === 1) {
          $contenu .= "\nUne nouvelle idée de cadeau a été proposée pour $nomUtilisateur :\n\n";
        } else {
          $contenu .= "\nDe nouvelles idées de cadeaux ont été proposées pour $nomUtilisateur :\n\n";
        }
        foreach($ideesUtilisateur->creations as $idee) {
          $contenu .= "  > {$idee->getDescription()}\n";
        }
      }

      if (count($ideesUtilisateur->suppressions) > 0) {
        if (count($ideesUtilisateur->suppressions) === 1) {
          $contenu .= "\nL'idée de cadeau pour $nomUtilisateur ci-dessous a été retirée de sa liste :\n\n";
        } else {
          $contenu .= "\nLes idées de cadeau pour $nomUtilisateur ci-dessous ont été retirées de sa liste :\n\n";
        }
        foreach($ideesUtilisateur->suppressions as $idee) {
          $contenu .= "  > {$idee->getDescription()}\n";
        }
      }

      if ((count($ideesUtilisateur->creations) > 0) || (count($ideesUtilisateur->suppressions) > 0)) {
        $contenu .= "\nPour consulter la liste d'idée de $nomUtilisateur,\n";
        $contenu .= "rendez-vous sur {$this->getUri('/idee', "idUtilisateur={$ideesUtilisateur->id}")}\n";
      }
    }

    return $this->envoieMail(
      $destinataire->getEmail(),
      "Actualités Tkdo",
      <<<EOS
Bonjour {$destinataire->getNom()},
$contenu
{$this->settings['signature']}
EOS
    );
  }

  public function envoieMailTirageFait(
    Utilisateur $destinataire,
    Occasion $occasion
  ): bool
  {
    return $this->envoieMail(
      $destinataire->getEmail(),
      "Tirage au sort fait pour {$occasion->getTitre()}",
      <<<EOS
Bonjour {$destinataire->getNom()},

Le tirage au sort est fait pour '{$occasion->getTitre()}' !

Pour découvrir à qui vous aurez le plaisir de faire un cadeau,
rendez-vous sur {$this->getUri("/occasion/{$occasion->getId()}")}

{$this->settings['signature']}
EOS
    );
  }

  private function getUri(string $path = '', string $query = '')
  {
    return $this->baseUri->withPath($this->baseUri->getPath() . $path)->withQuery($query);
  }
}
