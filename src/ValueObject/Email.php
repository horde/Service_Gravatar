<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Stringable;

/**
 * Represents an email address for Gravatar operations.
 *
 * Value object with validation and Gravatar hash generation.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
final readonly class Email implements Stringable
{
    private string $address;

    private function __construct(string $address)
    {
        $normalized = strtolower(trim($address));

        if (empty($normalized)) {
            throw new InvalidEmailException('Email address cannot be empty');
        }

        // Basic email validation
        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException("Invalid email address: $address");
        }

        $this->address = $normalized;
    }

    /**
     * Create from string.
     */
    public static function fromString(string $address): self
    {
        return new self($address);
    }

    /**
     * Get the normalized email address.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Get the domain part of the email.
     */
    public function getDomain(): string
    {
        $parts = explode('@', $this->address, 2);
        return $parts[1] ?? '';
    }

    /**
     * Get the local part of the email.
     */
    public function getLocalPart(): string
    {
        $parts = explode('@', $this->address, 2);
        return $parts[0] ?? '';
    }

    /**
     * Get the Gravatar hash for this email.
     *
     * The hash is the MD5 of the lowercase, trimmed email address.
     */
    public function getGravatarHash(): string
    {
        return md5($this->address);
    }

    public function toString(): string
    {
        return $this->address;
    }

    public function __toString(): string
    {
        return $this->address;
    }
}
