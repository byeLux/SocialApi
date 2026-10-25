<?php

declare(strict_types=1);

namespace SocialApi\Api;

use InvalidArgumentException;
use LogicException;
use SocialApi\Domain\Models\User;

class Request
{
    private ?User $user = null;
    private ?string $token = null;

    public function __construct(
        private string $method,
        private string $path,
        private string $body = '',
        private array $headers = [],
        private array $query = []
    ) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public static function fromGlobals(): Request
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headers = array_change_key_case($headers, CASE_LOWER);
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        if($authorization !== null) {
            $headers['authorization'] = $authorization;
        }

        if(array_key_exists('route', $_GET)) {
            $path = $_GET['route'];
            if(!is_string($path) || $path === '' || $path[0] !== '/'
                || preg_match('/[\x00-\x20\x7F?#]/', $path)) {
                throw new InvalidArgumentException('invalid_route');
            }
        } else {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        }

        return new Request(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            is_string($path) ? $path : '/',
            (string) file_get_contents('php://input'),
            $headers,
            $_GET
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function getJson(): array
    {
        try {
            $value = json_decode($this->body, false, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('invalid_json');
        }

        if(!$value instanceof \stdClass) {
            throw new InvalidArgumentException('invalid_json');
        }
        return (array) $value;
    }

    public function getString(array $body, string $key): string
    {
        if(!isset($body[$key]) || !is_string($body[$key])) {
            throw new InvalidArgumentException('invalid_' . $key);
        }
        return $body[$key];
    }

    public function getPage(): int
    {
        return $this->getPositiveInteger($this->query['page'] ?? '1', 'invalid_page');
    }

    public function getPositiveInteger(mixed $value, string $error = 'invalid_id'): int
    {
        if((!is_string($value) && !is_int($value))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
            || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException($error);
        }
        return (int) $value;
    }

    public function authenticate(User $user, string $token): void
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function getUser(): User
    {
        if($this->user === null) {
            throw new LogicException('Authentication middleware required.');
        }
        return $this->user;
    }

    public function getToken(): string
    {
        if($this->token === null) {
            throw new LogicException('Authentication middleware required.');
        }
        return $this->token;
    }
}
