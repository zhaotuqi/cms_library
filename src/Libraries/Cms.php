<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:39
 */

namespace App\Libraries;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;

class Cms
{
    const CMS_CONFIG_KEY = 'cms_keys';

    protected $redisCMS;
    protected $configCMS;

    public function init()
    {
        //更新cms redis连接
        Config::set('database.redis.cms_qa', [
            'host'     => '10.2.1.28',
            'password' => null,
            'port'     => 6380,
            'database' => 0
        ]);

        Config::set('database.redis.cms_on', [
            'host'     => '10.10.34.151',
            'password' => null,
            'port'     => 6379,
            'database' => 0
        ]);

        if (!$this->redisCMS) {
            $this->redisCMS = Redis::connection('cms_' . env('REDIS_CMS_ENV'));
        }

        if (!$this->configCMS) {
            $this->configCMS = config('cms.' . env('REDIS_CMS_ENV'));
        }
    }

    public function getConfigFromCache($configKey)
    {
        $this->init();

        $config = $this->configCMS[$configKey];
        $key    = 'cms_' . $config[0] . '_' . $config[1];

        return json_decode($this->redisCMS->hGet(CMS_CONFIG_KEY, $key), true);
    }
}