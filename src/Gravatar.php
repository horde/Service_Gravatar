<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar;

use Horde\Service\Gravatar\Exception\NetworkException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\AvatarResult;
use Horde\Service\Gravatar\ValueObject\Email;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;
use Horde\Service\Gravatar\ValueObject\GravatarProfile;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Gravatar service implementation.
 *
 * Provides dual API: simple facade for basic usage, rich domain model for advanced features.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class Gravatar implements GravatarInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly GravatarConfig $config = new GravatarConfig()
    ) {
    }

    // ========================================================================
    // Facade API - Simple string/array interface
    // ========================================================================

    /**
     * Get Gravatar hash for an email address.
     *
     * @param string $email Email address
     * @return string MD5 hash
     */
    public function getId(string $email): string
    {
        return Email::fromString($email)->getGravatarHash();
    }

    /**
     * Get avatar URL for an email address.
     *
     * @param string $email Email address
     * @param array<string, mixed>|int $options Options array or size integer
     * @return string Avatar URL
     */
    public function getAvatarUrl(string $email, array|int $options = []): string
    {
        $emailObj = Email::fromString($email);
        $optionsObj = $this->normalizeOptions($options);

        $result = $this->getAvatarWithMetadata($emailObj, $optionsObj);
        return $result->getUrl();
    }

    /**
     * Get profile URL for an email address.
     *
     * @param string $email Email address
     * @return string Profile URL
     */
    public function getProfileUrl(string $email): string
    {
        $hash = Email::fromString($email)->getGravatarHash();
        return $this->config->baseUrl . '/' . $hash;
    }

    /**
     * Fetch profile as raw JSON string.
     *
     * @param string $email Email address
     * @return string JSON profile data
     * @throws ProfileNotFoundException
     * @throws NetworkException
     */
    public function fetchProfile(string $email): string
    {
        $url = $this->getProfileUrl($email) . '.json';

        try {
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() === 404) {
                throw new ProfileNotFoundException("Profile not found for: $email");
            }

            if ($response->getStatusCode() !== 200) {
                throw new NetworkException(
                    "HTTP error {$response->getStatusCode()}: {$response->getReasonPhrase()}"
                );
            }

            return (string) $response->getBody();
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get profile as parsed array.
     *
     * @param string $email Email address
     * @return array<string, mixed> Profile data
     * @throws ProfileNotFoundException
     * @throws NetworkException
     */
    public function getProfile(string $email): array
    {
        $json = $this->fetchProfile($email);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new NetworkException('Invalid JSON response from Gravatar');
        }

        return $data;
    }

    /**
     * Fetch avatar image as stream.
     *
     * @param string $email Email address
     * @param array<string, mixed>|int $options Options array or size integer
     * @return StreamInterface|null Stream or null if not found (404)
     * @throws NetworkException
     */
    public function fetchAvatar(string $email, array|int $options = []): ?StreamInterface
    {
        $url = $this->getAvatarUrl($email, $options);

        try {
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() === 404) {
                return null;
            }

            if ($response->getStatusCode() !== 200) {
                throw new NetworkException(
                    "HTTP error {$response->getStatusCode()}: {$response->getReasonPhrase()}"
                );
            }

            return $response->getBody();
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    // ========================================================================
    // Domain API - Rich value objects
    // ========================================================================

    /**
     * Get avatar with full metadata.
     *
     * @param Email $email Email value object
     * @param AvatarOptions $options Avatar options
     * @return AvatarResult Complete result with metadata
     */
    public function getAvatarWithMetadata(Email $email, AvatarOptions $options): AvatarResult
    {
        $url = $this->buildAvatarUrl($email, $options);

        return new AvatarResult($url, $email, $options);
    }

    /**
     * Get profile as value object.
     *
     * @param Email $email Email value object
     * @return GravatarProfile Profile object
     * @throws ProfileNotFoundException
     * @throws NetworkException
     */
    public function getProfileObject(Email $email): GravatarProfile
    {
        $data = $this->getProfile($email->getAddress());
        return new GravatarProfile($data);
    }

    // ========================================================================
    // Internal helpers
    // ========================================================================

    /**
     * Normalize options from facade API to domain object.
     *
     * @param array<string, mixed>|int $options
     */
    private function normalizeOptions(array|int $options): AvatarOptions
    {
        if (is_int($options)) {
            return AvatarOptions::default()->withSize($options);
        }

        $avatarOptions = AvatarOptions::default();

        if (isset($options['size'])) {
            $avatarOptions = $avatarOptions->withSize((int) $options['size']);
        }

        if (isset($options['rating'])) {
            $rating = \Horde\Service\Gravatar\ValueObject\Rating::from($options['rating']);
            $avatarOptions = $avatarOptions->withRating($rating);
        }

        if (isset($options['default'])) {
            $default = $options['default'];
            // Try to parse as enum, otherwise use as custom URL
            try {
                $default = \Horde\Service\Gravatar\ValueObject\DefaultImage::from($default);
            } catch (\ValueError) {
                // Not an enum value, use as-is (custom URL)
            }
            $avatarOptions = $avatarOptions->withDefault($default);
        }

        if (isset($options['forceDefault'])) {
            $avatarOptions = $avatarOptions->withForceDefault((bool) $options['forceDefault']);
        }

        return $avatarOptions;
    }

    /**
     * Build avatar URL from email and options.
     */
    private function buildAvatarUrl(Email $email, AvatarOptions $options): string
    {
        $url = $this->config->baseUrl . '/avatar/' . $email->getGravatarHash();

        $params = $options->toQueryParams();
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
