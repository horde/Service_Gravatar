<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\ValueObject\GravatarConfig;
use PHPUnit\Framework\TestCase;

/**
 * Tests for GravatarConfig value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class GravatarConfigTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $config = new GravatarConfig();

        $this->assertEquals('http://www.gravatar.com', $config->baseUrl);
        $this->assertEquals(10, $config->timeout);
    }

    public function testCustomConstruction(): void
    {
        $config = new GravatarConfig('https://custom.gravatar.com', 30);

        $this->assertEquals('https://custom.gravatar.com', $config->baseUrl);
        $this->assertEquals(30, $config->timeout);
    }

    public function testStandardFactory(): void
    {
        $config = GravatarConfig::standard();

        $this->assertEquals('http://www.gravatar.com', $config->baseUrl);
        $this->assertEquals(10, $config->timeout);
    }

    public function testSecureFactory(): void
    {
        $config = GravatarConfig::secure();

        $this->assertEquals('https://secure.gravatar.com', $config->baseUrl);
        $this->assertEquals(10, $config->timeout);
    }

    public function testWithBaseUrl(): void
    {
        $config = GravatarConfig::standard();
        $newConfig = $config->withBaseUrl('https://example.com');

        // Original unchanged
        $this->assertEquals('http://www.gravatar.com', $config->baseUrl);

        // New has new URL
        $this->assertEquals('https://example.com', $newConfig->baseUrl);
        $this->assertEquals(10, $newConfig->timeout);
    }

    public function testWithTimeout(): void
    {
        $config = GravatarConfig::standard();
        $newConfig = $config->withTimeout(60);

        // Original unchanged
        $this->assertEquals(10, $config->timeout);

        // New has new timeout
        $this->assertEquals(60, $newConfig->timeout);
        $this->assertEquals('http://www.gravatar.com', $newConfig->baseUrl);
    }

    public function testImmutability(): void
    {
        $config1 = GravatarConfig::standard();
        $config2 = $config1->withTimeout(20);
        $config3 = $config2->withBaseUrl('https://test.com');

        // Each is independent
        $this->assertEquals(10, $config1->timeout);
        $this->assertEquals(20, $config2->timeout);
        $this->assertEquals(20, $config3->timeout);
        $this->assertEquals('http://www.gravatar.com', $config1->baseUrl);
        $this->assertEquals('http://www.gravatar.com', $config2->baseUrl);
        $this->assertEquals('https://test.com', $config3->baseUrl);
    }

    public function testReadonlyProperties(): void
    {
        $config = GravatarConfig::standard();

        $this->expectException(\Error::class);

        // @phpstan-ignore-next-line - Testing readonly enforcement
        $config->baseUrl = 'https://evil.com';
    }
}
