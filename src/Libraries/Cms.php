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
    const CMS_CONFIG_KEY = 'cms_keys';

    protected $redisCMS;
    protected $configCMS;

    public function __construct()
    {
        $this->redisCMS  = Redis::connection('cms_' . env('REDIS_CMS_ENV'));
        $this->configCMS = config('cms.' . env('REDIS_CMS_ENV'));
    }

    public function getConfigFromCache($configKey)
    {
        $config = $this->configCMS[$configKey];
        $key    = 'cms_' . $config[0] . '_' . $config[1];

        return json_decode($this->redisCMS->hGet(CMS_CONFIG_KEY, $key), true);
    }
}