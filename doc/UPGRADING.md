# Upgrading to Service_Gravatar 2.0

## Overview

Version 2.0 introduces a modern PSR-4 implementation with native PSR-7/PSR-17/PSR-18 support. The legacy PSR-0 `lib/` wrappers remain for backward compatibility.

## Breaking Changes

### Namespace Change
```php
// Old (Horde 5)
use Horde_Service_Gravatar;

// New (PSR-4)
use Horde\Service\Gravatar\Gravatar;
```

### Exception Handling
```php
// Old - Extended Horde_Exception (supports PEAR_Error)
catch (Horde_Exception $e)

// New - Extends RuntimeException (pure PHP exceptions)
use Horde\Service\Gravatar\GravatarException;
use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\Exception\NetworkException;

catch (ProfileNotFoundException $e) {
    // Specific exception for 404 errors
}
```

### HTTP Client
```php
// Old - Horde_Http_Client
$client = new Horde_Http_Client();
$gravatar = new Horde_Service_Gravatar(
    Horde_Service_Gravatar::STANDARD,
    $client
);

// New - PSR-18 ClientInterface + PSR-17 RequestFactoryInterface
use Horde\Service\Gravatar\Gravatar;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

$gravatar = new Gravatar($httpClient, $requestFactory);

// Or with config
$config = GravatarConfig::standard(); // or ::secure()
$gravatar = new Gravatar($httpClient, $requestFactory, $config);
```

### Return Types

#### getAvatarUrl()
```php
// Old - Returns Horde_Url object
$url = $gravatar->getAvatarUrl($mail, $opts);
echo $url; // Horde_Url has __toString()

// New - Returns string
$url = $gravatar->getAvatarUrl($email, $options);
echo $url; // Already a string
```

#### fetchAvatar()
```php
// Old - Returns resource or null
$resource = $gravatar->fetchAvatar($mail, $opts);
if ($resource) {
    $data = stream_get_contents($resource);
}

// New - Returns PSR-7 StreamInterface or null
$stream = $gravatar->fetchAvatar($email, $options);
if ($stream) {
    $data = (string) $stream;
}
```

#### getProfile()
```php
// Old - Returns array
$profile = $gravatar->getProfile($mail);
$name = $profile['entry'][0]['displayName'];

// New - Returns array (facade) or GravatarProfile (domain)
// Facade API:
$profile = $gravatar->getProfile($email);
$name = $profile['entry'][0]['displayName'];

// Domain API:
use Horde\Service\Gravatar\ValueObject\Email;

$email = Email::fromString('user@example.com');
$profile = $gravatar->getProfileObject($email);
$name = $profile->getDisplayName(); // Convenient accessor!
```

### Constants
```php
// Old - Class constants for base URLs
Horde_Service_Gravatar::STANDARD
Horde_Service_Gravatar::SECURE

// New - Config factory methods
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

GravatarConfig::standard()  // http://www.gravatar.com
GravatarConfig::secure()    // https://secure.gravatar.com
```

## Migration Strategies

### Strategy 1: Keep Using Legacy Wrappers
Minimal changes required. The PSR-0 wrappers in `lib/` will delegate to the modern implementation:

```php
// Still works in Horde 6
use Horde_Service_Gravatar;

$client = new Horde_Http_Client();
$gravatar = new Horde_Service_Gravatar(
    Horde_Service_Gravatar::STANDARD,
    $client
);

$avatarUrl = $gravatar->getAvatarUrl('user@example.com', 80);
$profile = $gravatar->getProfile('user@example.com');
```

**Note:** Legacy wrappers will be deprecated in a future major version.

### Strategy 2: Gradual Migration (Facade API)
Use modern namespace with minimal code changes:

```php
use Horde\Service\Gravatar\Gravatar;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

// Standard (HTTP)
$config = GravatarConfig::standard();
$gravatar = new Gravatar($httpClient, $requestFactory, $config);

// All facade methods return primitives
$hash = $gravatar->getId('user@example.com');
$url = $gravatar->getAvatarUrl('user@example.com', 80);
$profile = $gravatar->getProfile('user@example.com');
$stream = $gravatar->fetchAvatar('user@example.com', 80);
```

### Strategy 3: Full Modern Implementation (Domain API)
Leverage rich value objects for better type safety:

```php
use Horde\Service\Gravatar\Gravatar;
use Horde\Service\Gravatar\ValueObject\Email;
use Horde\Service\Gravatar\ValueObject\AvatarOptions;
use Horde\Service\Gravatar\ValueObject\Rating;
use Horde\Service\Gravatar\ValueObject\DefaultImage;
use Horde\Service\Gravatar\ValueObject\GravatarConfig;

$config = GravatarConfig::secure();
$gravatar = new Gravatar($httpClient, $requestFactory, $config);

// Create value objects
$email = Email::fromString('user@example.com');

$options = AvatarOptions::default()
    ->withSize(150)
    ->withRating(Rating::PG)
    ->withDefault(DefaultImage::IDENTICON);

// Use domain API
$result = $gravatar->getAvatarWithMetadata($email, $options);
echo $result->getUrl();
echo $result->getHash();
echo $result->getEmail()->getDomain();

$profile = $gravatar->getProfileObject($email);
echo $profile->getDisplayName();
echo $profile->getProfileUrl();
foreach ($profile->getAccounts() as $account) {
    echo $account['shortname'] . ': ' . $account['url'];
}
```

## Removed Features

### Horde_String Dependency
The PSR-4 variant uses native PHP functions instead of Horde_String:

```php
// Old
$normalized = Horde_String::lower(trim($mail));

// New (internal)
$normalized = strtolower(trim($mail));
```

### PEAR_Error Support
PSR-4 exceptions extend `RuntimeException`, not `Horde_Exception_Wrapped`:

```php
// Old - PEAR_Error supported
try {
    $profile = $gravatar->getProfile($mail);
} catch (Horde_Exception $e) {
    // Could catch PEAR_Error wrapped exceptions
}

// New - Pure PHP exceptions
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\Exception\NetworkException;

try {
    $profile = $gravatar->getProfile($email);
} catch (ProfileNotFoundException $e) {
    // Profile not found (404)
} catch (NetworkException $e) {
    // HTTP error or network failure
}
```

### Horde_Url Return Type
The facade API returns strings, not Horde_Url objects:

```php
// Old
$url = $gravatar->getAvatarUrl($mail, 80);
$url->add('extra', 'param'); // Horde_Url methods

// New
$url = $gravatar->getAvatarUrl($email, 80);
// Just a string, parse with parse_url() if needed
```

## New Features

### Enums for Type Safety
```php
use Horde\Service\Gravatar\ValueObject\Rating;
use Horde\Service\Gravatar\ValueObject\DefaultImage;

$options = AvatarOptions::default()
    ->withRating(Rating::PG)              // Type-safe enum
    ->withDefault(DefaultImage::RETRO);   // Type-safe enum
```

### Email Value Object
```php
$email = Email::fromString('User@EXAMPLE.com');
echo $email->getAddress();       // 'user@example.com' (normalized)
echo $email->getDomain();        // 'example.com'
echo $email->getLocalPart();     // 'user'
echo $email->getGravatarHash();  // MD5 hash
```

### Granular Exceptions
```php
use Horde\Service\Gravatar\Exception\InvalidEmailException;
use Horde\Service\Gravatar\Exception\InvalidOptionsException;
use Horde\Service\Gravatar\Exception\ProfileNotFoundException;
use Horde\Service\Gravatar\Exception\NetworkException;

try {
    $email = Email::fromString('invalid');
} catch (InvalidEmailException $e) {
    // Bad email format
}

try {
    $options = new AvatarOptions(size: 9999);
} catch (InvalidOptionsException $e) {
    // Size out of bounds
}
```

### Immutable Value Objects
All configuration is immutable with fluent interfaces:

```php
$options1 = AvatarOptions::default();
$options2 = $options1->withSize(100);
$options3 = $options2->withRating(Rating::G);

// Each is independent
assert($options1->size === null);
assert($options2->size === 100);
assert($options3->size === 100 && $options3->rating === Rating::G);
```

### Profile Object with Accessors
```php
// Old - Manual array access
$profile = $gravatar->getProfile($mail);
$name = $profile['entry'][0]['displayName'] ?? '';
$photos = $profile['entry'][0]['photos'] ?? [];

// New - Convenient accessors
$profile = $gravatar->getProfileObject($email);
$name = $profile->getDisplayName();
$photos = $profile->getPhotos();
$accounts = $profile->getAccounts();
$isEmpty = $profile->isEmpty();
```

## Benefits of Upgrading

1. **Type Safety** - Strict types, enums, readonly properties
2. **Modern Standards** - PSR-7/17/18 HTTP, PSR-4 autoloading
3. **Better Testing** - 108 tests with 214 assertions
4. **Value Objects** - Immutable, validated domain objects
5. **Progressive Disclosure** - Simple facade + rich domain APIs
6. **No Legacy Baggage** - Pure modern PHP 8.1+, no PEAR dependencies
7. **Granular Exceptions** - Specific exception types for better error handling
8. **Better IDE Support** - Full type hints and autocomplete

## Comparison Table

| Feature | Horde 5 (PSR-0) | Horde 6 (PSR-4) |
|---------|-----------------|-----------------|
| PHP Version | 7.4+ | 8.1+ |
| Namespace | `Horde_Service_Gravatar` | `Horde\Service\Gravatar` |
| HTTP Client | Horde_Http_Client | PSR-18 ClientInterface |
| Exceptions | Horde_Exception | RuntimeException + specific types |
| Return Types | Mixed (Horde_Url, resource) | Typed (string, StreamInterface) |
| Configuration | Constructor params | Immutable value objects |
| Profile Access | Array access | Object methods + array |
| Type Safety | Minimal | Strict types, enums, readonly |
| Test Coverage | 13 tests | 108 tests |

## Timeline

- **2.0.0** (Current) - PSR-4 implementation, legacy wrappers maintained
- **3.0.0** (Future) - Legacy wrappers deprecated with E_USER_DEPRECATED notices
- **4.0.0** (Future) - Legacy wrappers removed

## Support

For migration assistance, see:
- [README.md](../README.md) - Usage examples
- [Unit Tests](../test/unit/) - Working examples
- [Gravatar API Documentation](https://gravatar.com/site/implement/)
- [Horde Mailing List](https://lists.horde.org/)
