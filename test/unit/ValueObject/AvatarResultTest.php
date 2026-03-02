<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\AvatarResult;
use Horde\Service\Gravatar\ValueObject\DefaultImage;
use Horde\Service\Gravatar\ValueObject\Email;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AvatarResult value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class AvatarResultTest extends TestCase
{
    public function testConstruction(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default()->withSize(80);
        $url = 'https://www.gravatar.com/avatar/hash?s=80';

        $result = new AvatarResult($url, $email, $options);

        $this->assertEquals($url, $result->url);
        $this->assertSame($email, $result->email);
        $this->assertSame($options, $result->options);
    }

    public function testGetUrl(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default();
        $url = 'https://www.gravatar.com/avatar/0c17bf66e649070167701d2d3cd71711';

        $result = new AvatarResult($url, $email, $options);

        $this->assertEquals($url, $result->getUrl());
    }

    public function testGetHash(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default();
        $url = 'https://www.gravatar.com/avatar/hash';

        $result = new AvatarResult($url, $email, $options);

        $this->assertEquals('0c17bf66e649070167701d2d3cd71711', $result->getHash());
    }

    public function testGetEmail(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default();
        $url = 'https://www.gravatar.com/avatar/hash';

        $result = new AvatarResult($url, $email, $options);

        $this->assertSame($email, $result->getEmail());
        $this->assertEquals('test@example.org', $result->getEmail()->getAddress());
    }

    public function testGetOptions(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default()
            ->withSize(100)
            ->withDefault(DefaultImage::IDENTICON);
        $url = 'https://www.gravatar.com/avatar/hash';

        $result = new AvatarResult($url, $email, $options);

        $this->assertSame($options, $result->getOptions());
        $this->assertEquals(100, $result->getOptions()->size);
        $this->assertEquals(DefaultImage::IDENTICON, $result->getOptions()->default);
    }

    public function testReadonlyProperties(): void
    {
        $email = Email::fromString('test@example.org');
        $options = AvatarOptions::default();
        $result = new AvatarResult('http://example.com', $email, $options);

        $this->expectException(\Error::class);

        // @phpstan-ignore-next-line - Testing readonly enforcement
        $result->url = 'https://evil.com';
    }
}
