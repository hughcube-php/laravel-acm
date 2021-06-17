<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/6/17
 * Time: 2:15 下午
 */

namespace HughCube\Laravel\ACM\Tests\Commands;


use HughCube\Laravel\ACM\ACM;
use HughCube\Laravel\ACM\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class SyncConfigTest extends TestCase
{
    public function testHandle()
    {
        $dataId = md5(random_bytes(100));
        $content = md5(random_bytes(100));
        $file = sprintf("/tmp/%s", md5(random_bytes(100)));

        $this->assertTrue(ACM::client()->write($dataId, $content));

        Artisan::call('acm:sync-config', [
            'dataId' => $dataId,
            'file' => $file,
            '--repeat' => 10,
            '--AccessKeyID' => ACM::client()->getAccessKeyID(),
            '--AccessKeySecret' => ACM::client()->getAccessKeySecret(),
            '--Namespace' => ACM::client()->getNamespace(),
            '--Group' => ACM::client()->getGroup(),
        ]);

        $this->assertSame(file_get_contents($file), $content);
        $this->assertTrue(ACM::client()->remove($dataId));
    }
}
