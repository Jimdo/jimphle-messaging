<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\DataStructure\Map;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Authorization\TokenContext;

class TokenContextTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
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
     * @expectedException \Jimphle\Exception\InvalidRequestException
     */
    public function itShouldNotGrantAccessIfNoAuthorizationIsSet()
    {
        $this->authorizationContext->assertAccessIsGranted($this->validRequest());
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\InvalidRequestException
     */
    public function itShouldNotGrantAccessIfAccessTokenIsEmpty()
    {
        $this->authorizationContext->setJsonWebToken('');
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\InvalidAccessTokenException
     */
    public function itShouldNotGrantAccessIfTokenDataCanNotBeExtracted()
    {
        $this->websiteIdExtractor = $this->accessTokenExtractorMock();
        $this->websiteIdExtractor->expects($this->once())
            ->method('extractPayload')
            ->with(self::SOME_ACCESS_TOKEN)
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->loadAuthorizationContextWithValidToken();
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\AccessNotGrantedException
     * @expectedExceptionMessage my constraint failed
     */
    public function itShouldNotGrantAccessIfValidationOfTheGivenConstraintFails()
    {
        $constraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
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
        $constraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
        $constraint->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $otherConstraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
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
        $constraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function accessTokenExtractorMock()
    {
        return $this->getMock('\Jimphle\Messaging\Plugin\Authorization\JsonWebTokenExtractor');
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
