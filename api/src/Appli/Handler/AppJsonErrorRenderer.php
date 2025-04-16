<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Appli\Handler;

use App\Appli\Service\JsonService;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Exception\HttpSpecializedException;
use Throwable;

class AppJsonErrorRenderer extends JsonErrorRenderer
{
    public function __construct(private readonly JsonService $jsonService)
    {
    }

    #[\Override]
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof HttpSpecializedException) {
            return $this->jsonService->encodeException($exception);
        }
        return parent::__invoke($exception, $displayErrorDetails);
    }
}
