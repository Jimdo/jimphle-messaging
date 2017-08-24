<?php
namespace Jimphle\Messaging\Plugin\Authorization;

class TokenContext implements \Jimphle\Messaging\Plugin\Authorization\Context
{
    const WEBSITE_USER = 1;
    const SUPER_USER = 2;

    /**
     * @var \Jimphle\DataStructure\Map
     */
    private $token;

    private $accessTokenExtractor;

    public function __construct(\Jimphle\Messaging\Plugin\Authorization\JsonWebTokenExtractor $accessTokenExtractor)
    {
        $this->accessTokenExtractor = $accessTokenExtractor;
    }

    public function assertAccessIsGranted(\Jimphle\Messaging\Message $message, array $constraints = array())
    {
        if (!$this->token) {
            throw new \Jimphle\Exception\InvalidRequestException('no access token set');
        }

        if (count($constraints) == 0 || $this->isSuperUser()) {
            return null;
        }

        /**
         * @var \Jimphle\Messaging\Plugin\Authorization\Constraint $constraint
         */
        foreach ($constraints as $constraint) {
            $value = new \Jimphle\DataStructure\Map(
                array(
                    'accessToken' => $this->token,
                    'request' => new \Jimphle\DataStructure\Map($message->getPayload())
                )
            );
            if (!$constraint->validate($value)) {
                throw new \Jimphle\Exception\AccessNotGrantedException($constraint->getErrorMessage());
            }
        }
    }

    public function setJsonWebToken($token)
    {
        if (!$token) {
            throw new \Jimphle\Exception\InvalidRequestException('empty access token set');
        }
        try {
            $accessTokenPayload = new \Jimphle\DataStructure\Map(
                $this->accessTokenExtractor->extractPayload($token)
            );
            $scopes = isset($accessTokenPayload->scopes) ? $accessTokenPayload->scopes: [];
            if (isset($accessTokenPayload->w)) {
                $this->setToken(
                    new \Jimphle\DataStructure\Map(
                        array(
                            'role' => self::WEBSITE_USER,
                            'websiteId' => $accessTokenPayload->w,
                            'clientId' => $accessTokenPayload->aud,
                            'scopes' => $scopes,
                        )
                    )
                );
            } else {
                $this->setToken(
                    new \Jimphle\DataStructure\Map(
                        array(
                            'role' => self::SUPER_USER,
                            'clientId' => $accessTokenPayload->aud,
                            'scopes' => $scopes,
                        )
                    )
                );
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Jimphle\Exception\InvalidAccessTokenException($e->getMessage());
        }
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(\Jimphle\DataStructure\Map $token)
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->token->role == self::SUPER_USER;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isClient($id)
    {
        return $this->token->clientId == $id;
    }
}
