<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar;

use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\AvatarResult;
use Horde\Service\Gravatar\ValueObject\Email;
use Horde\Service\Gravatar\ValueObject\GravatarProfile;
use Psr\Http\Message\StreamInterface;

/**
 * Interface for Gravatar service.
 *
 * Provides dual API: simple facade + rich domain model.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
interface GravatarInterface
{
    // ========================================================================
    // Facade API - Simple string/scalar interface
    // ========================================================================

    /**
     * Get Gravatar hash for an email address.
     */
    public function getId(string $email): string;

    /**
     * Get avatar URL for an email address.
     *
     * @param array<string, mixed>|int $options Options array or size integer
     */
    public function getAvatarUrl(string $email, array|int $options = []): string;

    /**
     * Get profile URL for an email address.
     */
    public function getProfileUrl(string $email): string;

    /**
     * Fetch profile as raw JSON string.
     */
    public function fetchProfile(string $email): string;

    /**
     * Get profile as parsed array.
     *
     * @return array<string, mixed>
     */
    public function getProfile(string $email): array;

    /**
     * Fetch avatar image as stream.
     *
     * @param array<string, mixed>|int $options Options array or size integer
     */
    public function fetchAvatar(string $email, array|int $options = []): ?StreamInterface;

    // ========================================================================
    // Domain API - Rich value objects
    // ========================================================================

    /**
     * Get avatar with full metadata.
     */
    public function getAvatarWithMetadata(Email $email, AvatarOptions $options): AvatarResult;

    /**
     * Get profile as value object.
     */
    public function getProfileObject(Email $email): GravatarProfile;
}
