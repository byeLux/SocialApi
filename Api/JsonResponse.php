<?php

declare(strict_types=1);

namespace SocialApi\Api;

use InvalidArgumentException;
use UnexpectedValueException;
use DomainException;
use OutOfBoundsException;
use OverflowException;
use RuntimeException;
use Throwable;

class JsonResponse
{
    public function __construct(
        private array $data = [],
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function fromException(Throwable $error): JsonResponse
    {
        $status = 500;
        $message = 'server_error';

        if(get_class($error) === InvalidArgumentException::class) {
            $status = 400;
        } elseif(get_class($error) === UnexpectedValueException::class) {
            $status = 401;
        } elseif(get_class($error) === DomainException::class) {
            $status = 403;
        } elseif(get_class($error) === OutOfBoundsException::class) {
            $status = 404;
        } elseif(get_class($error) === OverflowException::class) {
            $status = 409;
        } elseif(get_class($error) === RuntimeException::class
            && in_array($error->getCode(), [401, 403, 404, 409], true)) {
            $status = $error->getCode();
        }

        if($status !== 500) {
            $message = $error->getMessage();
        } else {
            // Non registrare messaggi che possono contenere query o credenziali.
            error_log('SocialApi internal error: ' . get_class($error));
        }

        $headers = [];
        if($status === 401) {
            $headers['WWW-Authenticate'] = 'Bearer';
        }

        return new JsonResponse(['error' => $message], $status, $headers);
    }

    public function send(): void
    {
        // Serializzare prima degli header permette di gestire anche gli errori JSON.
        $body = '';
        if($this->status !== 204) {
            $body = json_encode($this->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Cache-Control: no-store');

        foreach($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $body;
    }
}
