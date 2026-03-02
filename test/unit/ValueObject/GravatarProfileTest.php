<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\Test\Unit\ValueObject;

use Horde\Service\Gravatar\ValueObject\GravatarProfile;
use PHPUnit\Framework\TestCase;

/**
 * Tests for GravatarProfile value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
class GravatarProfileTest extends TestCase
{
    private function getSampleData(): array
    {
        return [
            'entry' => [
                [
                    'displayName' => 'John Doe',
                    'profileUrl' => 'https://gravatar.com/johndoe',
                    'preferredUsername' => 'johndoe',
                    'photos' => [
                        [
                            'value' => 'https://gravatar.com/avatar/hash',
                            'type' => 'thumbnail',
                        ],
                    ],
                    'accounts' => [
                        [
                            'domain' => 'twitter.com',
                            'display' => '@johndoe',
                            'url' => 'https://twitter.com/johndoe',
                            'username' => 'johndoe',
                            'verified' => 'true',
                            'shortname' => 'twitter',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testGetDisplayName(): void
    {
        $profile = new GravatarProfile($this->getSampleData());

        $this->assertEquals('John Doe', $profile->getDisplayName());
    }

    public function testGetDisplayNameEmpty(): void
    {
        $profile = new GravatarProfile(['entry' => [[]]]);

        $this->assertEquals('', $profile->getDisplayName());
    }

    public function testGetPhotos(): void
    {
        $profile = new GravatarProfile($this->getSampleData());
        $photos = $profile->getPhotos();

        $this->assertIsArray($photos);
        $this->assertCount(1, $photos);
        $this->assertEquals('https://gravatar.com/avatar/hash', $photos[0]['value']);
        $this->assertEquals('thumbnail', $photos[0]['type']);
    }

    public function testGetPhotosEmpty(): void
    {
        $profile = new GravatarProfile(['entry' => [[]]]);

        $this->assertEquals([], $profile->getPhotos());
    }

    public function testGetProfileUrl(): void
    {
        $profile = new GravatarProfile($this->getSampleData());

        $this->assertEquals('https://gravatar.com/johndoe', $profile->getProfileUrl());
    }

    public function testGetPreferredUsername(): void
    {
        $profile = new GravatarProfile($this->getSampleData());

        $this->assertEquals('johndoe', $profile->getPreferredUsername());
    }

    public function testGetAccounts(): void
    {
        $profile = new GravatarProfile($this->getSampleData());
        $accounts = $profile->getAccounts();

        $this->assertIsArray($accounts);
        $this->assertCount(1, $accounts);
        $this->assertEquals('twitter.com', $accounts[0]['domain']);
        $this->assertEquals('@johndoe', $accounts[0]['display']);
        $this->assertEquals('https://twitter.com/johndoe', $accounts[0]['url']);
    }

    public function testToArray(): void
    {
        $data = $this->getSampleData();
        $profile = new GravatarProfile($data);

        $this->assertEquals($data, $profile->toArray());
    }

    public function testIsEmpty(): void
    {
        $emptyProfile = new GravatarProfile([]);
        $validProfile = new GravatarProfile($this->getSampleData());

        $this->assertTrue($emptyProfile->isEmpty());
        $this->assertFalse($validProfile->isEmpty());
    }

    public function testIsEmptyWithEmptyEntry(): void
    {
        $profile = new GravatarProfile(['entry' => []]);

        $this->assertTrue($profile->isEmpty());
    }

    public function testMultipleAccounts(): void
    {
        $data = [
            'entry' => [
                [
                    'accounts' => [
                        ['domain' => 'twitter.com', 'display' => '@user1', 'url' => '', 'username' => '', 'verified' => '', 'shortname' => ''],
                        ['domain' => 'github.com', 'display' => 'user1', 'url' => '', 'username' => '', 'verified' => '', 'shortname' => ''],
                        ['domain' => 'example.com', 'display' => 'user1', 'url' => '', 'username' => '', 'verified' => '', 'shortname' => ''],
                    ],
                ],
            ],
        ];

        $profile = new GravatarProfile($data);
        $accounts = $profile->getAccounts();

        $this->assertCount(3, $accounts);
        $this->assertEquals('twitter.com', $accounts[0]['domain']);
        $this->assertEquals('github.com', $accounts[1]['domain']);
        $this->assertEquals('example.com', $accounts[2]['domain']);
    }
}
