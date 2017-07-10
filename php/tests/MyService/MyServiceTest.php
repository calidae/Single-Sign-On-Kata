<?php

namespace MyService;

use SSO\Request;
use SSO\SingleSignOnRegistry;
use SSO\SSOToken;
use SSO\AuthenticationGateway;
use Exception;

class MyAuthenticationGateway implements AuthenticationGateway
{
    public function credentialsAreValid($username, $password){
        return $username == $password;
    }
}

class SSOTokenFake extends SSOToken
{
}


class SSORegistryFake implements SingleSignOnRegistry
{
    private $token;

    public function __construct() {
        $this->token = null;
    }

    public function isValid(SSOToken $token) {
        return $this->token == $token;
    }

    public function registerNewSession($username, $password) {
        $gateway = new MyAuthenticationGateway();
        if ($gateway->credentialsAreValid($username, $password)) {
            $this->token = new SSOTokenFake();
            return $this->token;
        } else {
            throw new Exception('Division by zero.');
        }
    }

    public function unregister(SSOToken $token) {
        $this->token = null;
    }
}


class MyCreateStub implements SingleSignOnRegistry
{
    public function __construct(bool $response) {
        $this->response = $response;
    }

    public function isValid(SSOToken $token) {
        return $this->response;
    }

    public function registerNewSession($username, $password) {}
    public function unregister(SSOToken $token) {}
}



class MyServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidSSOTokenIsRejectedStubMARC()
    {
        $ssoStub = new MyCreateStub(false);

        $ssoTokenStub = $this->createMock(SSOToken::class);

        $myService = new MyService($ssoStub);
        $response = $myService->handleRequest(new Request("Foo", $ssoTokenStub));
        $this->assertNotEquals("hello Foo!", $response->getText());
    }
    
    public function testInvalidSSOTokenIsRejectedStub()
    {
        $ssoStub = new MyCreateStub(false);

        $ssoTokenStub = $this->createMock(SSOToken::class);

        $myService = new MyService($ssoStub);
        $response = $myService->handleRequest(new Request("Foo", $ssoTokenStub));
        $this->assertNotEquals("hello Foo!", $response->getText());
    }
    
    public function testInvalidSSOTokenIsRejected()
    {
        // Create a stub for the SomeClass class.
        $ssoStub = $this->createMock(SingleSignOnRegistry::class);

        // Configure the stub.
        $ssoStub->method('isValid')
             ->willReturn(false);

        $ssoTokenStub = $this->createMock(SSOToken::class);

        $myService = new MyService($ssoStub);
        $response = $myService->handleRequest(new Request("Foo", $ssoTokenStub));
        $this->assertNotEquals("hello Foo!", $response->getText());
    }

    public function testValidSSOTokenIsAccepted()
    {
        $reg = new SSORegistryFake();
        $token = $reg->registerNewSession('MarcLovesAngel', 'MarcLovesAngel');

        $myService = new MyService($reg);
        
        $response = $myService->handleRequest(new Request("Foo", $token));
        $this->assertEquals("hello Foo!", $response->getText());
    }

    public function testInvalidSSOTokenIsRejectedAdria()
    {
        $reg = new SSORegistryFake();
        
        $this->expectException(Exception::class);
        $token = $reg->registerNewSession('Adria', 'MarcLovesAngel');
    }
}
