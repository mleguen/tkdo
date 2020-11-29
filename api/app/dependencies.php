<?php
declare(strict_types=1);

use App\Application\Service\AuthService;
use App\Application\Service\MailerService;
use App\Application\Service\PasswordService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        AuthService::class => function (ContainerInterface $c) {
            return new AuthService($c->get('settings')['auth']);
        },
        MailerService::class => function (ContainerInterface $c) {
            return new MailerService($c->get('settings')['mailer']);
        },
        PasswordService::class => function () {
            return new PasswordService();
        },
    ]);
};
