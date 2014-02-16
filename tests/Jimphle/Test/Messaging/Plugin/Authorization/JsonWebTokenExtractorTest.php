<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\Messaging\Plugin\Authorization\JsonWebTokenExtractor;

class JsonWebTokenExtractorTest extends \PHPUnit_Framework_TestCase
{
    const SOME_ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJpYXQiOjEzNzA5NTUyNjMsImV4cCI6MTM3MzU0NzI2MywiYXVkIjoiaW9zIiwidyI6OTAwMDkzODM1fQ.-78opnbSE6D57d-AdO5-IERAaEo6avMP-gSBaoenA3s';

    /**
     * @var JsonWebTokenExtractor
     */
    private $extractor;

    public function setUp()
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing token.
     */
    public function itShouldThrowAnExceptionIfAccessTokenIsMissing()
    {
        $this->extractor->extractPayload('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid data.
     */
    public function itShouldThrowAnExceptionIfAccessTokenIsInvalid()
    {
        $this->extractor->extractPayload('asdasd.asdasd.asdasd');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid token.
     */
    public function itShouldThrowAnExceptionIfAccessTokenLooksNotLikeAnJWT()
    {
        $this->extractor->extractPayload('asdasd.asdasd');
    }
}
