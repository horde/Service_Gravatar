# Horde Service_Gravatar

Modern PHP library for Gravatar profile and avatar services with native PSR-7/PSR-17/PSR-18 support.

## Installation

```bash
composer require horde/service-gravatar
```

## Requirements

- PHP 8.1+
- PSR-18 HTTP client implementation
- PSR-17 HTTP factory implementation

## Quick Start

```php
use Horde\Service\Gravatar\Gravatar;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

// Simple facade API
$gravatar = new Gravatar($httpClient, $requestFactory);

// Get Gravatar hash
$hash = $gravatar->getId('user@example.com');
// Returns: '0c83f57c786a0b4a39efab23731c7ebc'

// Get avatar URL
$avatarUrl = $gravatar->getAvatarUrl('user@example.com', 80);
// Returns: 'http://www.gravatar.com/avatar/hash?s=80'

// Get profile data
$profile = $gravatar->getProfile('user@example.com');
// Returns: array with profile information
```

## Secure/HTTPS Mode

```php
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

$config = GravatarConfig::secure();
$gravatar = new Gravatar($httpClient, $requestFactory, $config);

$avatarUrl = $gravatar->getAvatarUrl('user@example.com', 80);
// Returns: 'https://secure.gravatar.com/avatar/hash?s=80'
```

## Architecture

This library provides two API levels for progressive disclosure:

### Facade API
Simple string-based interface for basic usage:

```php
// Get hash
$hash = $gravatar->getId('user@example.com');

// Get avatar URL with size
$url = $gravatar->getAvatarUrl('user@example.com', 80);

// Get avatar URL with options
$url = $gravatar->getAvatarUrl('user@example.com', [
    'size' => 100,
    'rating' => 'pg',
    'default' => 'identicon',
    'forceDefault' => false,
]);

// Fetch avatar image as stream
$stream = $gravatar->fetchAvatar('user@example.com', 80);

// Get profile as array
$profile = $gravatar->getProfile('user@example.com');
```

### Domain API
Rich value objects with detailed metadata:

```php
use Horde\Service\Gravatar\ValueObject\Email;
use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\Rating;
use Horde\Service\Gravatar\ValueObject\DefaultImage;

// Create email value object
$email = Email::fromString('user@example.com');
echo $email->getDomain();        // 'example.com'
echo $email->getLocalPart();     // 'user'
echo $email->getGravatarHash();  // MD5 hash

// Configure avatar options
$options = AvatarOptions::default()
    ->withSize(200)
    ->withRating(Rating::PG)
    ->withDefault(DefaultImage::IDENTICON)
    ->withForceDefault(false);

// Get avatar with complete metadata
$result = $gravatar->getAvatarWithMetadata($email, $options);
echo $result->getUrl();                    // Full URL
echo $result->getHash();                   // Gravatar hash
echo $result->getEmail()->getAddress();    // Original email
echo $result->getOptions()->size;          // 200

// Get profile as object
$profile = $gravatar->getProfileObject($email);
echo $profile->getDisplayName();           // Display name
echo $profile->getProfileUrl();            // Profile URL
print_r($profile->getPhotos());            // Photo array
print_r($profile->getAccounts());          // Social accounts
```

## Value Objects

### Email
Represents an email address with validation and Gravatar hash:
```php
$email = Email::fromString('user@example.com');
$email->getAddress();        // Normalized address
$email->getDomain();         // Domain part
$email->getLocalPart();      // Local part
$email->getGravatarHash();   // MD5 hash
```

### AvatarOptions
Immutable configuration for avatar requests:
```php
$options = AvatarOptions::default()
    ->withSize(150)                              // 1-2048 pixels
    ->withRating(Rating::G)                      // G, PG, R, X
    ->withDefault(DefaultImage::MYSTERY_PERSON)  // or custom URL
    ->withForceDefault(false);
```

### Rating Enum
Content rating levels:
- `Rating::G` - Suitable for all websites
- `Rating::PG` - May contain rude gestures, provocative dress
- `Rating::R` - May contain profanity, intense violence, nudity
- `Rating::X` - May contain hardcore sexual imagery

### DefaultImage Enum
Default image options when avatar not found:
- `DefaultImage::NOT_FOUND` - Return 404
- `DefaultImage::MYSTERY_PERSON` - Silhouette (mm)
- `DefaultImage::IDENTICON` - Geometric pattern
- `DefaultImage::MONSTERID` - Generated monster
- `DefaultImage::WAVATAR` - Generated face
- `DefaultImage::RETRO` - 8-bit pixelated face
- `DefaultImage::BLANK` - Transparent PNG

Or use a custom URL:
```php
$options->withDefault('https://example.com/default-avatar.png');
```

### GravatarProfile
Parsed profile data with convenient accessors:
```php
$profile = $gravatar->getProfileObject($email);

$profile->getDisplayName();        // Display name
$profile->getPreferredUsername();  // Username
$profile->getProfileUrl();         // Profile URL
$profile->getPhotos();             // Array of photos
$profile->getAccounts();           // Social accounts
$profile->isEmpty();               // Check if profile exists
$profile->toArray();               // Raw data
```

### GravatarConfig
Service configuration:
```php
// Standard HTTP
$config = GravatarConfig::standard();

// Secure HTTPS
$config = GravatarConfig::secure();

// Custom
$config = new GravatarConfig('https://custom.gravatar.com', 30);
$config = $config->withTimeout(60);
```

## Exception Handling

All exceptions extend `GravatarException`:

```php
use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Horde\Service\Gravatar\Exception\InvalidOptionsException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\Exception\NetworkException;

try {
    $profile = $gravatar->getProfile('user@example.com');
} catch (ProfileNotFoundException $e) {
    // Profile doesn't exist (404)
} catch (NetworkException $e) {
    // HTTP error or network failure
} catch (InvalidEmailException $e) {
    // Invalid email format
} catch (InvalidOptionsException $e) {
    // Invalid avatar options
}
```

## Examples

### Get Avatar with Custom Default

```php
use Horde\Service\Gravatar\ValueObject\DefaultImage;

$url = $gravatar->getAvatarUrl('newuser@example.com', [
    'size' => 80,
    'default' => DefaultImage::IDENTICON,
]);
```

### Fetch Avatar Image

```php
$stream = $gravatar->fetchAvatar('user@example.com', 120);

if ($stream) {
    // Save to file
    file_put_contents('avatar.jpg', (string) $stream);
} else {
    // Avatar not found (404)
}
```

### Check Profile Existence

```php
try {
    $profile = $gravatar->getProfileObject($email);

    if ($profile->isEmpty()) {
        echo "Profile exists but has no data";
    } else {
        echo "Display name: " . $profile->getDisplayName();
    }
} catch (ProfileNotFoundException $e) {
    echo "Profile not found";
}
```

### Force Default Image

```php
// Always show default, never fetch actual Gravatar
$options = AvatarOptions::default()
    ->withSize(100)
    ->withDefault(DefaultImage::RETRO)
    ->withForceDefault(true);

$result = $gravatar->getAvatarWithMetadata($email, $options);
```

## Advanced Usage

### Custom HTTP Configuration

```php
// Use any PSR-18 client
$httpClient = new YourPsr18Client([
    'timeout' => 30,
    'headers' => ['User-Agent' => 'MyApp/1.0'],
]);

$requestFactory = new YourPsr17RequestFactory();

$gravatar = new Gravatar($httpClient, $requestFactory);
```

### Multiple Avatars

```php
$emails = ['user1@example.com', 'user2@example.com', 'user3@example.com'];
$options = AvatarOptions::default()->withSize(80);

$avatars = [];
foreach ($emails as $emailStr) {
    $email = Email::fromString($emailStr);
    $result = $gravatar->getAvatarWithMetadata($email, $options);
    $avatars[$emailStr] = $result->getUrl();
}
```

### Profile Social Accounts

```php
$profile = $gravatar->getProfileObject($email);

foreach ($profile->getAccounts() as $account) {
    echo "{$account['shortname']}: {$account['url']}\n";
}
```

## Legacy Support

The PSR-0 `lib/` directory provides backward compatibility wrappers for Horde 5 applications using `Horde_Service_Gravatar`. New code should use the modern `Horde\Service\Gravatar` namespace with native PSR interfaces.

See [UPGRADING.md](doc/UPGRADING.md) for migration instructions.

## Testing

```bash
composer install
vendor/bin/phpunit
```

**Test Coverage:** 108 tests, 214 assertions

## Documentation

- [UPGRADING.md](doc/UPGRADING.md) - Migration guide from Horde 5
- [Gravatar API Documentation](https://gravatar.com/site/implement/)
- [Horde Framework Documentation](https://dev.horde.org/)

## License

LGPL 2.1 - See LICENSE file for details.

## Contributing

Contributions are welcome! Please follow:
- PHP 8.1+ with strict types
- PER-1 coding style
- Comprehensive unit tests
- PHPUnit 12+ with attributes
- Conventional Commits for commit messages

## Authors

- Gunnar Wrobel <wrobel@pardus.de> - Original author
- Michael Slusarz - Lead maintainer
