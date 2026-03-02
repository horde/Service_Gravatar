<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit;

use Horde\Service\Gravatar\GravatarException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for GravatarException.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class GravatarExceptionTest extends TestCase
{
    public function testBasicException(): void
    {
        $exception = new GravatarException('Test message');

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new GravatarException('Test message', 404);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new GravatarException('Wrapped exception', 0, $previous);

        $this->assertEquals('Wrapped exception', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new GravatarException('Test');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(GravatarException::class);
        $this->expectExceptionMessage('Thrown exception');

        throw new GravatarException('Thrown exception');
    }
}
