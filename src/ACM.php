<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:31 下午.
 */

namespace HughCube\Laravel\ACM;

use HughCube\Laravel\ACM\Client\Client;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * Class Package.
 *
 * @method static Client client(string $name = null)
 *
 * @see \HughCube\Laravel\ACM\Manager
 * @see \HughCube\Laravel\ACM\ServiceProvider
 */
class ACM extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'acm';
    }
}
