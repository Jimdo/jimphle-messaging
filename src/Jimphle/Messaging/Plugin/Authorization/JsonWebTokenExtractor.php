<?php
namespace Jimphle\Messaging\Plugin\Authorization;

class JsonWebTokenExtractor
{
    public function extractPayload($token)
    {
        if (!$token) {
            throw new \InvalidArgumentException('Missing token.');
        }
        $parts = explode('.', $token);

        if (count($parts) != 3) {
            throw new \InvalidArgumentException('Invalid token.');
        }
        list($header, $payload, $sig) = $parts;

        $data = json_decode($this->base64UrlDecode($payload), true);
        if (!$data) {
            throw new \InvalidArgumentException('Invalid data.');
        }
        return $data;
    }

    private function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_,', '+/='));
    }
}