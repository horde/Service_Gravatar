<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit;

use Horde\Service\Gravatar\Exception\NetworkException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\Gravatar;
use Horde\Service\Gravatar\GravatarInterface;
use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\DefaultImage;
use Horde\Service\Gravatar\ValueObject\Email;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;
use Horde\Service\Gravatar\ValueObject\Rating;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for Gravatar service.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class GravatarTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $gravatar = $this->createGravatar();

        $this->assertInstanceOf(GravatarInterface::class, $gravatar);
    }

    public function testGetId(): void
    {
        $gravatar = $this->createGravatar();

        $hash = $gravatar->getId('test@example.org');

        $this->assertEquals('0c17bf66e649070167701d2d3cd71711', $hash);
    }

    public function testGetIdNormalizes(): void
    {
        $gravatar = $this->createGravatar();

        $hash = $gravatar->getId(' Test@EXAMPLE.orG ');

        $this->assertEquals('0c17bf66e649070167701d2d3cd71711', $hash);
    }

    public function testGetProfileUrl(): void
    {
        $gravatar = $this->createGravatar();

        $url = $gravatar->getProfileUrl('test@example.org');

        $this->assertEquals(
            'http://www.gravatar.com/0c17bf66e649070167701d2d3cd71711',
            $url
        );
    }

    public function testGetProfileUrlSecure(): void
    {
        $config = GravatarConfig::secure();
        $gravatar = $this->createGravatar($config);

        $url = $gravatar->getProfileUrl('test@example.org');

        $this->assertEquals(
            'https://secure.gravatar.com/0c17bf66e649070167701d2d3cd71711',
            $url
        );
    }

    public function testGetAvatarUrlSimple(): void
    {
        $gravatar = $this->createGravatar();

        $url = $gravatar->getAvatarUrl('test@example.org');

        $this->assertEquals(
            'http://www.gravatar.com/avatar/0c17bf66e649070167701d2d3cd71711',
            $url
        );
    }

    public function testGetAvatarUrlWithSizeInteger(): void
    {
        $gravatar = $this->createGravatar();

        $url = $gravatar->getAvatarUrl('test@example.org', 80);

        $this->assertStringContainsString('s=80', $url);
    }

    public function testGetAvatarUrlWithOptionsArray(): void
    {
        $gravatar = $this->createGravatar();

        $url = $gravatar->getAvatarUrl('test@example.org', [
            'size' => 100,
            'rating' => 'pg',
            'default' => 'identicon',
        ]);

        $this->assertStringContainsString('s=100', $url);
        $this->assertStringContainsString('r=pg', $url);
        $this->assertStringContainsString('d=identicon', $url);
    }

    public function testGetAvatarUrlWithForceDefault(): void
    {
        $gravatar = $this->createGravatar();

        $url = $gravatar->getAvatarUrl('test@example.org', [
            'forceDefault' => true,
        ]);

        $this->assertStringContainsString('f=y', $url);
    }

    public function testFetchProfileSuccess(): void
    {
        $jsonResponse = '{"entry":[{"displayName":"John Doe"}]}';

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn($jsonResponse);
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $result = $gravatar->fetchProfile('test@example.org');

        $this->assertEquals($jsonResponse, $result);
    }

    public function testFetchProfileNotFound(): void
    {
        $this->expectException(ProfileNotFoundException::class);
        $this->expectExceptionMessage('Profile not found');

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(404);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $gravatar->fetchProfile('notfound@example.org');
    }

    public function testFetchProfileHttpError(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('HTTP error 500');

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getReasonPhrase')->willReturn('Internal Server Error');
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $gravatar->fetchProfile('test@example.org');
    }

    public function testGetProfile(): void
    {
        $jsonResponse = '{"entry":[{"displayName":"John Doe"}]}';

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn($jsonResponse);
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $profile = $gravatar->getProfile('test@example.org');

        $this->assertIsArray($profile);
        $this->assertEquals('John Doe', $profile['entry'][0]['displayName']);
    }

    public function testFetchAvatarSuccess(): void
    {
        $imageData = 'fake-image-data';

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $result = $gravatar->fetchAvatar('test@example.org');

        $this->assertInstanceOf(StreamInterface::class, $result);
    }

    public function testFetchAvatarNotFound(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(404);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $result = $gravatar->fetchAvatar('notfound@example.org');

        $this->assertNull($result);
    }

    public function testGetAvatarWithMetadata(): void
    {
        $gravatar = $this->createGravatar();
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default()->withSize(120);

        $result = $gravatar->getAvatarWithMetadata($email, $options);

        $this->assertStringContainsString('0c17bf66e649070167701d2d3cd71711', $result->getUrl());
        $this->assertStringContainsString('s=120', $result->getUrl());
        $this->assertEquals('0c17bf66e649070167701d2d3cd71711', $result->getHash());
        $this->assertSame($email, $result->getEmail());
        $this->assertSame($options, $result->getOptions());
    }

    public function testGetProfileObject(): void
    {
        $jsonResponse = '{"entry":[{"displayName":"John Doe","profileUrl":"https://gravatar.com/john"}]}';

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn($jsonResponse);
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $gravatar = new Gravatar($httpClient, $requestFactory);
        $email = Email::fromString('test@example.org');
        $profile = $gravatar->getProfileObject($email);

        $this->assertEquals('John Doe', $profile->getDisplayName());
        $this->assertEquals('https://gravatar.com/john', $profile->getProfileUrl());
    }

    public function testCustomConfig(): void
    {
        $config = new GravatarConfig('https://custom.gravatar.com', 30);
        $gravatar = $this->createGravatar($config);

        $url = $gravatar->getAvatarUrl('test@example.org');

        $this->assertStringStartsWith('https://custom.gravatar.com/avatar/', $url);
    }

    public function testUrlBuildingWithAllOptions(): void
    {
        $gravatar = $this->createGravatar();
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default()
            ->withSize(200)
            ->withRating(Rating::R)
            ->withDefault(DefaultImage::MONSTERID)
            ->withForceDefault(true);

        $result = $gravatar->getAvatarWithMetadata($email, $options);
        $url = $result->getUrl();

        $this->assertStringContainsString('s=200', $url);
        $this->assertStringContainsString('r=r', $url);
        $this->assertStringContainsString('d=monsterid', $url);
        $this->assertStringContainsString('f=y', $url);
    }

    private function createGravatar(?GravatarConfig $config = null): Gravatar
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        return $config
            ? new Gravatar($httpClient, $requestFactory, $config)
            : new Gravatar($httpClient, $requestFactory);
    }
}
