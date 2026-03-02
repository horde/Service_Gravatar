<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

/**
 * Default image options for Gravatar.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
enum DefaultImage: string
{
    case NOT_FOUND = '404';          // Return 404 error if not found
    case MYSTERY_PERSON = 'mm';      // Mystery person silhouette
    case IDENTICON = 'identicon';    // Geometric pattern based on email hash
    case MONSTERID = 'monsterid';    // Generated monster with different colors, faces, etc
    case WAVATAR = 'wavatar';        // Generated faces with differing features and backgrounds
    case RETRO = 'retro';            // 8-bit arcade-style pixelated faces
    case BLANK = 'blank';            // Transparent PNG image
}
