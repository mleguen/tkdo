<?php
declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Service\MailerService;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Log\LoggerInterface;

abstract class IdeeActionANotifier extends IdeeAction
{
    protected $utilisateurRepository;
    protected $mailerService;

    public function __construct(
        LoggerInterface $logger,
        IdeeRepository $ideeRepository,
        UtilisateurRepository $utilisateurRepository,
        MailerService $mailerService
    ) {
        parent::__construct($logger, $ideeRepository);
        $this->utilisateurRepository = $utilisateurRepository;
        $this->mailerService = $mailerService;
    }
}
