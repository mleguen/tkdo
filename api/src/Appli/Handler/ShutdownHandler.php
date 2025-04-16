<?php
declare(strict_types=1);

namespace App\Appli\Handler;

use App\Appli\Settings\ErrorSettings;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\ErrorHandler;
use Slim\ResponseEmitter;

/**
 * Simulate Slim error handling before shutdown if Slim was unable to handle an error
 */
class ShutdownHandler
{
    private $errorHandler;

    public function __construct(
        private readonly ServerRequestInterface $request,
        ErrorHandler $errorHandler,
        private readonly ErrorSettings $errorSettings
    ) {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke()
    {
        $error = error_get_last();
        if ($error) {
            $errorFile = $error['file'];
            $errorLine = $error['line'];
            $errorMessage = $error['message'];
            $errorType = $error['type'];

            switch ($errorType) {
                case E_USER_ERROR:
                    $message = "FATAL ERROR: {$errorMessage}. ";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;

                case E_USER_WARNING:
                    $message = "WARNING: {$errorMessage}";
                    break;

                case E_USER_NOTICE:
                    $message = "NOTICE: {$errorMessage}";
                    break;

                default:
                    $message = "ERROR: {$errorMessage}";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;
            }

            $exception = new \Exception($message);
            $response = $this->errorHandler->__invoke(
                $this->request,
                $exception,
                $this->errorSettings->displayErrorDetails,
                $this->errorSettings->logErrors,
                $this->errorSettings->logErrorDetails
            );

            $responseEmitter = new ResponseEmitter();
            $responseEmitter->emit($response);
        }
    }
}
