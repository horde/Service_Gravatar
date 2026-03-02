<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\Exception;

use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Horde\Service\Gravatar\Exception\InvalidOptionsException;
use Horde\Service\Gravatar\Exception\NetworkException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\GravatarException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for specific exceptions.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class SpecificExceptionsTest extends TestCase
{
    public function testInvalidEmailException(): void
    {
        $exception = new InvalidEmailException('Invalid email');

        $this->assertInstanceOf(GravatarException::class, $exception);
        $this->assertEquals('Invalid email', $exception->getMessage());
    }

    public function testInvalidOptionsException(): void
    {
        $exception = new InvalidOptionsException('Invalid options');

        $this->assertInstanceOf(GravatarException::class, $exception);
        $this->assertEquals('Invalid options', $exception->getMessage());
    }

    public function testProfileNotFoundException(): void
    {
        $exception = new ProfileNotFoundException('Profile not found');

        $this->assertInstanceOf(GravatarException::class, $exception);
        $this->assertEquals('Profile not found', $exception->getMessage());
    }

    public function testNetworkException(): void
    {
        $exception = new NetworkException('Network error');

        $this->assertInstanceOf(GravatarException::class, $exception);
        $this->assertEquals('Network error', $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exceptions = [
            new InvalidEmailException('test'),
            new InvalidOptionsException('test'),
            new ProfileNotFoundException('test'),
            new NetworkException('test'),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(GravatarException::class, $exception);
            $this->assertInstanceOf(\RuntimeException::class, $exception);
            $this->assertInstanceOf(\Exception::class, $exception);
        }
    }

    public function testExceptionsWithPrevious(): void
    {
        $previous = new \RuntimeException('Original error');

        $email = new InvalidEmailException('Email error', 0, $previous);
        $options = new InvalidOptionsException('Options error', 0, $previous);
        $profile = new ProfileNotFoundException('Profile error', 0, $previous);
        $network = new NetworkException('Network error', 0, $previous);

        $this->assertSame($previous, $email->getPrevious());
        $this->assertSame($previous, $options->getPrevious());
        $this->assertSame($previous, $profile->getPrevious());
        $this->assertSame($previous, $network->getPrevious());
    }
}
