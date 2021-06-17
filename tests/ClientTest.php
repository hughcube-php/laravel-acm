<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/6/15
 * Time: 7:14 下午.
 */

namespace HughCube\Laravel\ACM\Tests;

use HughCube\Laravel\ACM\ACM;
use HughCube\Laravel\ACM\Client\Client;
use HughCube\Laravel\ACM\Client\Endpoint;

class ClientTest extends TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf(Client::class, ACM::client());
    }

    public function testEndpointHandler()
    {
        $this->assertInstanceOf(
            Endpoint::class,
            ACM::client()->getEndpointHandler()->randomUrl()
        );
    }

    public function testCRUD()
    {
        $dataId = md5(random_bytes(100));
        $content = md5(random_bytes(100));

        $this->assertTrue(ACM::client()->write($dataId, $content));
        $this->assertSame($content, ACM::client()->read($dataId));
        $this->assertTrue(ACM::client()->remove($dataId));
    }
}
