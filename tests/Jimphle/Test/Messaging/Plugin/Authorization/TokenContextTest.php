<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\DataStructure\Map;
use Jimphle\Exception\AccessNotGrantedException;
use Jimphle\Exception\InvalidAccessTokenException;
use Jimphle\Exception\InvalidRequestException;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Authorization\TokenContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TokenContextTest extends TestCase
{
    const SOME_INTERACTION = 'blaa_interaction';
    const SOME_ACCESS_TOKEN = 'some-token';
    const AUTHORIZED_WEBSITE_ID = 123456;
    const UNAUTHORIZED_WEBSITE_ID = 654321;
    const SOME_OTHER_INTERACTION = 'some-other-interaction';
    const CLIENT_ID = 'some-client';
    const SCOPES = 'web';

    public $websiteIdExtractor;

    /**
     * @var TokenContext
     */
    private $authorizationContext;

    public function setUp(): void
    {
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->any())
            ->method('extractPayload')
            ->will($this->returnValue($this->websiteUserTokenJwtPayload()));

        $this->authorizationContext = new TokenContext($this->websiteIdExtractor);
    }

    /**
     * @test
     */
    public function itShouldExtractAccessTokenData()
    {
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->once())
            ->method('extractPayload')
            ->with(self::SOME_ACCESS_TOKEN)
            ->will($this->returnValue($this->websiteUserTokenJwtPayload()));

        $this->loadAuthorizationContextWithValidToken();
    }

    /**
     * @test
     */
    public function itShouldNotGrantAccessIfNoAuthorizationIsSet()
    {
        $this->expectException(InvalidRequestException::class);
        $this->authorizationContext->assertAccessIsGranted($this->validRequest());
    }

    /**
     * @test
     */
    public function itShouldNotGrantAccessIfAccessTokenIsEmpty()
    {
        $this->expectException(InvalidRequestException::class);
        $this->authorizationContext->setJsonWebToken('');
    }

    /**
     * @test
     */
    public function itShouldNotGrantAccessIfTokenDataCanNotBeExtracted()
    {
        $this->expectException(InvalidAccessTokenException::class);
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->once())
            ->method('extractPayload')
            ->with(self::SOME_ACCESS_TOKEN)
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->loadAuthorizationContextWithValidToken();
    }

    /**
     * @test
     */
    public function itShouldNotGrantAccessIfValidationOfTheGivenConstraintFails()
    {
        $this->expectException(AccessNotGrantedException::class);
        $this->expectExceptionMessage('my constraint failed');
        $constraint = $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
        $constraint->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->valueToValidate(new Map(), $this->websiteUserToken())))
            ->will($this->returnValue(false));

        $constraint->expects($this->once())
            ->method('getErrorMessage')
            ->will($this->returnValue('my constraint failed'));

        $this->loadAuthorizationContextWithValidToken();

        $this->authorizationContext->assertAccessIsGranted($this->invalidRequest(), array($constraint));
    }

    /**
     * @test
     */
    public function itShouldValidateAgainstTheConstraints()
    {
        $constraint = $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
        $constraint->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $otherConstraint = $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
        $otherConstraint->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->loadAuthorizationContextWithValidToken();

        $this->authorizationContext->assertAccessIsGranted($this->validRequest(), array($constraint, $otherConstraint));
    }

    /**
     * @test
     */
    public function itShouldNotValidateAgainstTheConstraintsGivenASuperUser()
    {
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->any())
            ->method('extractPayload')
            ->will($this->returnValue($this->superUserTokenJwtPayload()));
        $constraint = $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
        $constraint->expects($this->never())
            ->method('validate');
        $this->loadAuthorizationContextWithValidToken();

        $this->authorizationContext->assertAccessIsGranted($this->validRequest(), array($constraint));
    }

    /**
     * @test
     */
    public function itShouldProvideTheWebsiteUserToken()
    {
        $this->loadAuthorizationContextWithValidToken();

        $this->assertThat(
            $this->authorizationContext->getToken(),
            $this->equalTo($this->websiteUserToken())
        );
        $this->assertThat(
            $this->authorizationContext->isSuperUser(),
            $this->isFalse()
        );
    }

    /**
     * @test
     */
    public function itShouldProvideTheSuperUserToken()
    {
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->any())
            ->method('extractPayload')
            ->will($this->returnValue($this->superUserTokenJwtPayload()));

        $this->loadAuthorizationContextWithValidToken();

        $this->assertThat(
            $this->authorizationContext->getToken(),
            $this->equalTo($this->superUserToken())
        );
        $this->assertThat(
            $this->authorizationContext->isSuperUser(),
            $this->isTrue()
        );
    }

    /**
     * @return Map
     */
    private function validRequest()
    {
        return GenericMessage::generateDummy();
    }

    private function loadAuthorizationContextWithValidToken()
    {
        $this->authorizationContext = new TokenContext($this->websiteIdExtractor);
        $this->authorizationContext->setJsonWebToken(self::SOME_ACCESS_TOKEN);
    }

    /**
     * @return MockObject
     */
    private function accessTokenExtractorMock()
    {
        return $this->createMock(\Jimphle\Messaging\Plugin\Authorization\JsonWebTokenExtractor::class);
    }

    private function invalidRequest()
    {
        $request = GenericMessage::generateDummy();
        return $request;
    }

    private function valueToValidate($request, $accessTokenPayload)
    {
        return new Map(
            array(
                'accessToken' => $accessTokenPayload,
                'request' => $request
            )
        );
    }

    private function websiteUserTokenJwtPayload()
    {
        return array('w' => self::AUTHORIZED_WEBSITE_ID, 'aud' => self::CLIENT_ID, 'scopes' => self::SCOPES);
    }

    private function websiteUserToken()
    {
        return new Map(
            array(
                'role' => TokenContext::WEBSITE_USER,
                'websiteId' => self::AUTHORIZED_WEBSITE_ID,
                'clientId' => self::CLIENT_ID,
                'scopes' => self::SCOPES,
            )
        );
    }

    private function superUserTokenJwtPayload()
    {
        return array('aud' => self::CLIENT_ID, 'scopes' => self::SCOPES);
    }

    private function superUserToken()
    {
        return new Map(
            array(
                'role' => TokenContext::SUPER_USER,
                'clientId' => self::CLIENT_ID,
                'scopes' => self::SCOPES,
            )
        );
    }
}
