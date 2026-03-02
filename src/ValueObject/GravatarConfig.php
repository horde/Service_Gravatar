<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

/**
 * Gravatar service configuration.
 *
 * Immutable configuration for base URL and timeouts.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
final readonly class GravatarConfig
{
    public function __construct(
        public string $baseUrl = 'http://www.gravatar.com',
        public int $timeout = 10
    ) {}

    /**
     * Standard Gravatar service (HTTP).
     */
    public static function standard(): self
    {
        return new self('http://www.gravatar.com');
    }

    /**
     * Secure Gravatar service (HTTPS).
     */
    public static function secure(): self
    {
        return new self('https://secure.gravatar.com');
    }

    /**
     * Change base URL.
     */
    public function withBaseUrl(string $baseUrl): self
    {
        return new self($baseUrl, $this->timeout);
    }

    /**
     * Change timeout.
     */
    public function withTimeout(int $timeout): self
    {
        return new self($this->baseUrl, $timeout);
    }
}
