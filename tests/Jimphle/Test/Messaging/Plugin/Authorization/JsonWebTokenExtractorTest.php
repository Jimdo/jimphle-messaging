<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\Exception\InvalidArgumentException;
use Jimphle\Messaging\Plugin\Authorization\JsonWebTokenExtractor;
use PHPUnit\Framework\TestCase;

class JsonWebTokenExtractorTest extends TestCase
{
    const SOME_ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJpYXQiOjEzNzA5NTUyNjMsImV4cCI6MTM3MzU0NzI2MywiYXVkIjoiaW9zIiwidyI6OTAwMDkzODM1fQ.-78opnbSE6D57d-AdO5-IERAaEo6avMP-gSBaoenA3s';

    /**
     * @var JsonWebTokenExtractor
     */
    private $extractor;

    public function setUp():void
    {
        $this->extractor = new JsonWebTokenExtractor();
    }

    /**
     * @test
     */
    public function itShouldExtractThePayloadFromAccessToken()
    {
        $this->assertThat(
            $this->extractor->extractPayload(self::SOME_ACCESS_TOKEN),
            $this->equalTo(
                array(
                    'iat' => 1370955263,
                    'exp' => 1373547263,
                    'aud' => 'ios',
                    'w' => 900093835,
                )
            )
        );
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAccessTokenIsMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing token.');
        $this->extractor->extractPayload('');
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAccessTokenIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid data.');
        $this->extractor->extractPayload('asdasd.asdasd.asdasd');
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAccessTokenLooksNotLikeAnJWT()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token.');
        $this->extractor->extractPayload('asdasd.asdasd');
    }
}
