<?php

declare(strict_types=1);

namespace Horde\Service\Gravatar\ValueObject;

/**
 * Gravatar content rating levels.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_Gravatar
 */
enum Rating: string
{
    case G = 'g';      // Suitable for display on all websites
    case PG = 'pg';    // May contain rude gestures, provocatively dressed individuals, lesser swear words, or mild violence
    case R = 'r';      // May contain such things as harsh profanity, intense violence, nudity, or hard drug use
    case X = 'x';      // May contain hardcore sexual imagery or extremely disturbing violence
}
