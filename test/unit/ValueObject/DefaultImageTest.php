<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\ValueObject\DefaultImage;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DefaultImage enum.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class DefaultImageTest extends TestCase
{
    public function testNotFound(): void
    {
        $image = DefaultImage::NOT_FOUND;

        $this->assertEquals('404', $image->value);
    }

    public function testMysteryPerson(): void
    {
        $image = DefaultImage::MYSTERY_PERSON;

        $this->assertEquals('mm', $image->value);
    }

    public function testIdenticon(): void
    {
        $image = DefaultImage::IDENTICON;

        $this->assertEquals('identicon', $image->value);
    }

    public function testMonsterid(): void
    {
        $image = DefaultImage::MONSTERID;

        $this->assertEquals('monsterid', $image->value);
    }

    public function testWavatar(): void
    {
        $image = DefaultImage::WAVATAR;

        $this->assertEquals('wavatar', $image->value);
    }

    public function testRetro(): void
    {
        $image = DefaultImage::RETRO;

        $this->assertEquals('retro', $image->value);
    }

    public function testBlank(): void
    {
        $image = DefaultImage::BLANK;

        $this->assertEquals('blank', $image->value);
    }

    public function testFromValidValue(): void
    {
        $image = DefaultImage::from('identicon');

        $this->assertEquals(DefaultImage::IDENTICON, $image);
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\ValueError::class);

        DefaultImage::from('invalid');
    }

    public function testAllDefaultImages(): void
    {
        $cases = DefaultImage::cases();

        $this->assertCount(7, $cases);
        $this->assertContains(DefaultImage::NOT_FOUND, $cases);
        $this->assertContains(DefaultImage::MYSTERY_PERSON, $cases);
        $this->assertContains(DefaultImage::IDENTICON, $cases);
        $this->assertContains(DefaultImage::MONSTERID, $cases);
        $this->assertContains(DefaultImage::WAVATAR, $cases);
        $this->assertContains(DefaultImage::RETRO, $cases);
        $this->assertContains(DefaultImage::BLANK, $cases);
    }
}
