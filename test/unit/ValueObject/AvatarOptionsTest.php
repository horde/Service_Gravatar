<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\Exception\InvalidOptionsException;
use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\DefaultImage;
use Horde\Service\Gravatar\ValueObject\Rating;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AvatarOptions value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class AvatarOptionsTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $options = new AvatarOptions();

        $this->assertNull($options->size);
        $this->assertNull($options->rating);
        $this->assertNull($options->default);
        $this->assertFalse($options->forceDefault);
    }

    public function testDefaultFactory(): void
    {
        $options = AvatarOptions::default();

        $this->assertInstanceOf(AvatarOptions::class, $options);
        $this->assertNull($options->size);
    }

    public function testWithSize(): void
    {
        $options = AvatarOptions::default();
        $newOptions = $options->withSize(80);

        // Original unchanged
        $this->assertNull($options->size);

        // New has size
        $this->assertEquals(80, $newOptions->size);
    }

    public function testWithRating(): void
    {
        $options = AvatarOptions::default();
        $newOptions = $options->withRating(Rating::PG);

        $this->assertNull($options->rating);
        $this->assertEquals(Rating::PG, $newOptions->rating);
    }

    public function testWithDefaultEnum(): void
    {
        $options = AvatarOptions::default();
        $newOptions = $options->withDefault(DefaultImage::IDENTICON);

        $this->assertNull($options->default);
        $this->assertEquals(DefaultImage::IDENTICON, $newOptions->default);
    }

    public function testWithDefaultCustomUrl(): void
    {
        $options = AvatarOptions::default();
        $newOptions = $options->withDefault('https://example.com/avatar.png');

        $this->assertNull($options->default);
        $this->assertEquals('https://example.com/avatar.png', $newOptions->default);
    }

    public function testWithForceDefault(): void
    {
        $options = AvatarOptions::default();
        $newOptions = $options->withForceDefault(true);

        $this->assertFalse($options->forceDefault);
        $this->assertTrue($newOptions->forceDefault);
    }

    public function testFluentChaining(): void
    {
        $options = AvatarOptions::default()
            ->withSize(100)
            ->withRating(Rating::R)
            ->withDefault(DefaultImage::RETRO)
            ->withForceDefault(true);

        $this->assertEquals(100, $options->size);
        $this->assertEquals(Rating::R, $options->rating);
        $this->assertEquals(DefaultImage::RETRO, $options->default);
        $this->assertTrue($options->forceDefault);
    }

    public function testImmutability(): void
    {
        $options1 = AvatarOptions::default();
        $options2 = $options1->withSize(50);
        $options3 = $options2->withRating(Rating::G);

        // Each is independent
        $this->assertNull($options1->size);
        $this->assertEquals(50, $options2->size);
        $this->assertEquals(50, $options3->size);
        $this->assertNull($options2->rating);
        $this->assertEquals(Rating::G, $options3->rating);
    }

    public function testToQueryParamsEmpty(): void
    {
        $options = AvatarOptions::default();
        $params = $options->toQueryParams();

        $this->assertIsArray($params);
        $this->assertEmpty($params);
    }

    public function testToQueryParamsSize(): void
    {
        $options = AvatarOptions::default()->withSize(120);
        $params = $options->toQueryParams();

        $this->assertEquals(['s' => '120'], $params);
    }

    public function testToQueryParamsRating(): void
    {
        $options = AvatarOptions::default()->withRating(Rating::PG);
        $params = $options->toQueryParams();

        $this->assertEquals(['r' => 'pg'], $params);
    }

    public function testToQueryParamsDefaultEnum(): void
    {
        $options = AvatarOptions::default()->withDefault(DefaultImage::MONSTERID);
        $params = $options->toQueryParams();

        $this->assertEquals(['d' => 'monsterid'], $params);
    }

    public function testToQueryParamsDefaultCustomUrl(): void
    {
        $options = AvatarOptions::default()->withDefault('https://example.com/default.png');
        $params = $options->toQueryParams();

        $this->assertEquals(['d' => 'https://example.com/default.png'], $params);
    }

    public function testToQueryParamsForceDefault(): void
    {
        $options = AvatarOptions::default()->withForceDefault(true);
        $params = $options->toQueryParams();

        $this->assertEquals(['f' => 'y'], $params);
    }

    public function testToQueryParamsComplete(): void
    {
        $options = AvatarOptions::default()
            ->withSize(200)
            ->withRating(Rating::X)
            ->withDefault(DefaultImage::BLANK)
            ->withForceDefault(true);

        $params = $options->toQueryParams();

        $this->assertEquals([
            's' => '200',
            'r' => 'x',
            'd' => 'blank',
            'f' => 'y',
        ], $params);
    }

    public function testSizeTooSmall(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('between 1 and 2048');

        new AvatarOptions(0);
    }

    public function testSizeTooLarge(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('between 1 and 2048');

        new AvatarOptions(2049);
    }

    public function testValidSizeBoundaries(): void
    {
        $options1 = new AvatarOptions(1);
        $options2 = new AvatarOptions(2048);

        $this->assertEquals(1, $options1->size);
        $this->assertEquals(2048, $options2->size);
    }

    public function testVariousSizes(): void
    {
        $sizes = [1, 50, 80, 100, 200, 512, 1024, 2048];

        foreach ($sizes as $size) {
            $options = AvatarOptions::default()->withSize($size);
            $this->assertEquals($size, $options->size);
        }
    }
}
