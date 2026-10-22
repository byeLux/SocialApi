<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Security;

use RuntimeException;
use SocialApi\Domain\Interfaces\Repositories\IAuthTokenRepository;
use SocialApi\Domain\Interfaces\Security\ITokenManager;

class TokenManager implements ITokenManager
{
    public function __construct(
        private IAuthTokenRepository $authTokenRepository,
        private string $secret
    ) {
        if(strlen($this->secret) < 32 || trim($this->secret) === '') {
            throw new RuntimeException('JWT_KEY requires at least 32 bytes.');
        }
    }

    private function decode(string $value): ?array
    {
        $value = base64_decode($value, true);
        if($value === false) {
            return null;
        }

        try {
            $array = json_decode($value, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if(is_array($array)) {
            return $array;
        }
        return null;
    }

    private function encode(array $array): string
    {
        return base64_encode(json_encode($array, JSON_THROW_ON_ERROR));
    }

    public function generate(int $userId): string
    {
        if($userId < 1) {
            throw new RuntimeException('Invalid user identity.');
        }

        $expiresAt = time() + 3600;
        $payload = $this->encode(
            [
                'id' => $userId,
                'exp' => $expiresAt
            ]
        );

        $signature = hash_hmac('sha512', $payload, $this->secret);

        $token = "{$signature}.{$payload}";
        $tokenHash = hash('sha256', $token);

        $this->authTokenRepository->register($tokenHash, $userId, $expiresAt);

        return $token;
    }

    public function validate(string $token): bool
    {
        return $this->getPayload($token) !== null;
    }

    public function invalidate(string $token): bool
    {
        $array = $this->getPayload($token);
        if($array === null) {
            return false;
        }

        $tokenHash = hash('sha256', $token);
        return $this->authTokenRepository->delete($tokenHash);
    }

    public function getId(string $token): ?int
    {
        $array = $this->getPayload($token);
        if($array !== null) {
            return $array['id'];
        }
        return null;
    }

    private function getPayload(string $token): ?array
    {
        if(strlen($token) > 4096) {
            return null;
        }

        $parts = explode('.', $token);
        if(count($parts) !== 2) {
            return null;
        }

        $signature = $parts[0];
        $payload = $parts[1];

        if(!hash_equals(hash_hmac('sha512', $payload, $this->secret), $signature)) {
            return null;
        }

        $array = $this->decode($payload);
        if($array === null || count($array) !== 2
            || !is_int($array['id'] ?? null) || $array['id'] < 1
            || !is_int($array['exp'] ?? null) || $array['exp'] <= time()) {
            return null;
        }

        $tokenHash = hash('sha256', $token);
        if(!$this->authTokenRepository->isValid($tokenHash, $array['id'], $array['exp'])) {
            return null;
        }

        return $array;
    }
}
