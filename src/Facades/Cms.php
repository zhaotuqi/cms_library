<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:35
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Cms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cms';
    }
}