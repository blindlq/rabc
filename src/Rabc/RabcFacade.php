<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/26
 * Time: 18:12
 */

namespace Junhai\Rabc;


use Illuminate\Support\Facades\Facade;

class RabcFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rabc';
    }

}