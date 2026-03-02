<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

/**
 * Avatar request result with metadata.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
final readonly class AvatarResult
{
    public function __construct(
        public string $url,
        public Email $email,
        public AvatarOptions $options
    ) {}

    /**
     * Get the avatar URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the Gravatar hash.
     */
    public function getHash(): string
    {
        return $this->email->getGravatarHash();
    }

    /**
     * Get the email address.
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * Get the options used for this avatar.
     */
    public function getOptions(): AvatarOptions
    {
        return $this->options;
    }
}
