<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

/**
 * Gravatar profile information.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
final readonly class GravatarProfile
{
    /**
     * @param array<string, mixed> $data Raw profile data from Gravatar API
     */
    public function __construct(
        private array $data
    ) {
    }

    /**
     * Get display name from profile.
     */
    public function getDisplayName(): string
    {
        return $this->data['entry'][0]['displayName'] ?? '';
    }

    /**
     * Get profile photos.
     *
     * @return array<array{value: string, type: string}>
     */
    public function getPhotos(): array
    {
        return $this->data['entry'][0]['photos'] ?? [];
    }

    /**
     * Get profile URL.
     */
    public function getProfileUrl(): string
    {
        return $this->data['entry'][0]['profileUrl'] ?? '';
    }

    /**
     * Get preferred username.
     */
    public function getPreferredUsername(): string
    {
        return $this->data['entry'][0]['preferredUsername'] ?? '';
    }

    /**
     * Get all accounts (social profiles, etc.).
     *
     * @return array<array{domain: string, display: string, url: string, username: string, verified: string, shortname: string}>
     */
    public function getAccounts(): array
    {
        return $this->data['entry'][0]['accounts'] ?? [];
    }

    /**
     * Get raw profile data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Check if profile has data.
     */
    public function isEmpty(): bool
    {
        return empty($this->data['entry']);
    }
}
