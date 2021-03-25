<?php
/**
 * Copyright 2011-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2011-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Service_Gravatar
 */
namespace Horde\Service\Gravatar;
use Horde_Test_Case as TestCase;

/**
 * @author    Gunnar Wrobel <wrobel@pardus.de>
 * @category  Horde
 * @copyright 2011-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Service_Gravatar
 */
class ServerTest extends TestCase
{
    private $_server;

    public function setUp(): void
    {
        $config = self::getConfig('SERVICE_GRAVATAR_TEST_CONFIG');
        if ($config && !empty($config['service']['gravatar']['server'])) {
            $this->_server = $config['service']['gravatar']['server'];
        } else {
            $this->markTestSkipped('Configuration is missing and remote server tests are disabled.');
        }
    }

    public function testGetProfile()
    {
        $g = new Horde_Service_Gravatar($this->_server);
        $profile = $g->getProfile('wrobel@horde.org');
        $this->assertEquals(
            'http://gravatar.com/abc1xyz2',
            $profile['entry'][0]['profileUrl']
        );
    }
}
