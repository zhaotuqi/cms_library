<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:39
 */

namespace App\Libraries;

use Illuminate\Support\Facades\Redis;

class Cms
{
    //cms在redis中的config_key
    const CMS_CONFIG_KEY = 'cms_keys';

    protected $redisCMS;

    public function __construct()
    {
        echo "=======";
        $this->redisCMS = Redis::connection('cms');
    }

    public function test()
    {
        echo "测试通过啦！\n";
        Redis::connection('cms');
//        var_dump($this->redisCMS);
    }

    public function getConfigFromCache($configId, $tag)
    {
        $key = 'cms_' . $configId . '_' . $tag;

//        return json_decode($this->redisCMS->hGet(CMS_CONFIG_KEY, $key), true);
    }
}