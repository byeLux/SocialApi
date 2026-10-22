<?php

declare(strict_types=1);

class TokenManager
{
    private function decode($value) : ?array
    {
        if($value) return json_decode(base64_decode($value), true);
        return null;
    }

    private function encode($array) : ?string
    {
        if($array) return base64_encode(json_encode($array));
        return null;
    }

    public function generate(int $userId) : string
    {
        $payload = 
            $this->encode(
                [
                    'id' => $userId,
                    'exp' => time() + 3600
                ]
            );

        $signature = hash_hmac('sha512', $payload, 'abc123');

        return "{$signature}.{$payload}";
    }

    public function validate(string $token) : bool
    {
        $parts = explode('.', $token);
        if(count($parts) === 2) {
            $signature = $parts[0];
            $payload = $parts[1];

            if(!hash_equals($signature, hash_hmac('sha512', $payload, 'abc123'))) 
            {
                return false;
            }

            $array = $this->decode($payload);
            $exp = $array['exp'];

            if(time() > $exp) 
            {
                return false;
            }

            return true;
        }
        return false;
    }

    public function invalidate(string $token) : bool
    {
        try {
            if($this->validate($token)) {
                $parts = explode('.', $token);
                $payload = $parts[1];
                $array = $this->decode($payload);
                $array['exp'] = time() - 3600;
                $newPayload = $this->encode($array);
            }
            return true;
        } catch (TypeError $e) {
            echo "Type Error: " . $e->getMessage();
            return false;
        } 
    }

    public function getId(string $token) : ?int
    {
        try {
            if($this->validate($token)) {
                $parts = explode('.', $token);
                $payload = $parts[1];
                $array = $this->decode($payload);
                return $array['id'];
            }
            return null;
        } catch (TypeError $e) {
            echo "Type Error: " . $e->getMessage();
            return null;
        } 
    }
}