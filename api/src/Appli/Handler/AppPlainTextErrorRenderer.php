<?php
declare(strict_types=1);

namespace App\Appli\Handler;

use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Exception\HttpSpecializedException;
use Throwable;

class AppPlainTextErrorRenderer extends PlainTextErrorRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof HttpSpecializedException) {
            return "HTTP {$exception->getCode()}: {$exception->getMessage()}";
        }
        return parent::__invoke($exception, $displayErrorDetails);
    }
}    
