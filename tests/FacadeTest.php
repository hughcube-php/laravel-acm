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

class FacadeTest extends TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf(Client::class, ACM::client());
    }
}
