<?php
/**
 * Created by PhpStorm.
 * User: polidog
 * Date: 2016/12/06
 */

namespace BRlab\OAuth2\Client\Test\Provider;

use PHPUnit\Framework\TestCase;
use BRlab\OAuth2\Client\Provider\YConnectResourceOwner;

class YConnectResourceOwnerTest extends TestCase
{
    /**
     * @test
     */
    public function notExistGetterMethodCalled()
    {
        $resource = new YConnectResourceOwner(['given_name' => 'polidog']);
        $actual = $resource->getGivenName();
        $this->assertEquals('polidog', $actual);
    }

    /**
     * @test
     */
    public function notExistUserId()
    {
        $resource = new YConnectResourceOwner([]);
        $this->assertNull($resource->getId());
    }
}