<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

use Horde\Service\Gravatar\Exception\InvalidOptionsException;

/**
 * Avatar request options.
 *
 * Immutable value object for avatar configuration.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
final readonly class AvatarOptions
{
    public function __construct(
        public ?int $size = null,
        public ?Rating $rating = null,
        public DefaultImage|string|null $default = null,
        public bool $forceDefault = false
    ) {
        if ($this->size !== null && ($this->size < 1 || $this->size > 2048)) {
            throw new InvalidOptionsException('Size must be between 1 and 2048 pixels');
        }
    }

    /**
     * Create default options.
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Set avatar size in pixels (1-2048).
     */
    public function withSize(int $size): self
    {
        return new self($size, $this->rating, $this->default, $this->forceDefault);
    }

    /**
     * Set content rating level.
     */
    public function withRating(Rating $rating): self
    {
        return new self($this->size, $rating, $this->default, $this->forceDefault);
    }

    /**
     * Set default image (enum value or custom URL).
     */
    public function withDefault(DefaultImage|string $default): self
    {
        return new self($this->size, $this->rating, $default, $this->forceDefault);
    }

    /**
     * Force display of default image even if Gravatar exists.
     */
    public function withForceDefault(bool $force = true): self
    {
        return new self($this->size, $this->rating, $this->default, $force);
    }

    /**
     * Convert to query parameters for Gravatar API.
     *
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->size !== null) {
            $params['s'] = (string) $this->size;
        }

        if ($this->rating !== null) {
            $params['r'] = $this->rating->value;
        }

        if ($this->default !== null) {
            $params['d'] = $this->default instanceof DefaultImage
                ? $this->default->value
                : $this->default;
        }

        if ($this->forceDefault) {
            $params['f'] = 'y';
        }

        return $params;
    }
}
