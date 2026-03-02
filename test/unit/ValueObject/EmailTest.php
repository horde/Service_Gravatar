<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Horde\Service\Gravatar\ValueObject\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Email value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class EmailTest extends TestCase
{
    public function testFromString(): void
    {
        $email = Email::fromString('test@example.org');

        $this->assertEquals('test@example.org', $email->getAddress());
    }

    public function testNormalization(): void
    {
        $email = Email::fromString(' Test@EXAMPLE.orG ');

        $this->assertEquals('test@example.org', $email->getAddress());
    }

    public function testGetDomain(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertEquals('example.com', $email->getDomain());
    }

    public function testGetLocalPart(): void
    {
        $email = Email::fromString('user@example.com');

        $this->assertEquals('user', $email->getLocalPart());
    }

    public function testGetGravatarHash(): void
    {
        $email = Email::fromString('test@example.org');

        // Known hash for this email
        $this->assertEquals('0c17bf66e649070167701d2d3cd71711', $email->getGravatarHash());
    }

    public function testToString(): void
    {
        $email = Email::fromString('test@example.org');

        $this->assertEquals('test@example.org', (string) $email);
        $this->assertEquals('test@example.org', $email->toString());
    }

    public function testInvalidEmailEmpty(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('cannot be empty');

        Email::fromString('');
    }

    public function testInvalidEmailFormat(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email address');

        Email::fromString('not-an-email');
    }

    /**
     * @param string $input
     * @param string $expected
     */
    #[DataProvider('provideValidEmails')]
    public function testValidEmails(string $input, string $expected): void
    {
        $email = Email::fromString($input);

        $this->assertEquals($expected, $email->getAddress());
    }

    public static function provideValidEmails(): array
    {
        return [
            ['test@example.org', 'test@example.org'],
            ['x@example.org', 'x@example.org'],
            ['test@example.com', 'test@example.com'],
            ['Test@Example.COM', 'test@example.com'],
            [' spaces@example.org ', 'spaces@example.org'],
        ];
    }

    /**
     * @param string $email
     * @param string $expectedHash
     */
    #[DataProvider('provideEmailHashes')]
    public function testKnownHashes(string $email, string $expectedHash): void
    {
        $emailObj = Email::fromString($email);

        $this->assertEquals($expectedHash, $emailObj->getGravatarHash());
    }

    public static function provideEmailHashes(): array
    {
        return [
            ['test@example.org', '0c17bf66e649070167701d2d3cd71711'],
            ['x@example.org', 'ae46d8cbbb834a85db7287f8342d0c42'],
            ['test@example.com', '55502f40dc8b7c769880b10874abc9d0'],
        ];
    }
}
