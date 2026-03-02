<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\ValueObject\Rating;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Rating enum.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class RatingTest extends TestCase
{
    public function testGRating(): void
    {
        $rating = Rating::G;

        $this->assertEquals('g', $rating->value);
    }

    public function testPGRating(): void
    {
        $rating = Rating::PG;

        $this->assertEquals('pg', $rating->value);
    }

    public function testRRating(): void
    {
        $rating = Rating::R;

        $this->assertEquals('r', $rating->value);
    }

    public function testXRating(): void
    {
        $rating = Rating::X;

        $this->assertEquals('x', $rating->value);
    }

    public function testFromValidValue(): void
    {
        $rating = Rating::from('pg');

        $this->assertEquals(Rating::PG, $rating);
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\ValueError::class);

        Rating::from('invalid');
    }

    public function testAllRatings(): void
    {
        $cases = Rating::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(Rating::G, $cases);
        $this->assertContains(Rating::PG, $cases);
        $this->assertContains(Rating::R, $cases);
        $this->assertContains(Rating::X, $cases);
    }
}
