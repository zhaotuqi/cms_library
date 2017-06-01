<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:39
 */

namespace App\Libraries;

use Common as CmsCommon;

defined('CMS_REDIS_DB') or define('CMS_REDIS_DB', [
    'qa'  => [
        'host'     => '10.2.1.28',
        'password' => null,
        'port'     => 6380,
        'database' => 0
    ],
    'pro' => [
        'host'     => '10.10.34.151',
        'password' => null,
        'port'     => 6379,
        'database' => 0
    ]
]);

defined('CMS_SERVER') or define('CMS_SERVER', [
    'qa'  => 'http://cms-qa02.xuebadev.com/v1/configs/',
    'pro' => 'http://10.10.84.32:86/v1/configs/'
]);

defined('CMS_CONFIG_KEY') or define('CMS_CONFIG_KEY', 'cms_keys');

class Cms
{
    protected $redisCMS;
    protected $configCMS;

    public function __construct()
    {
        if (!$this->redisCMS) {
            require base_path('vendor/predis/predis/autoload.php');

            $this->redisCMS = new \Predis\Client(CMS_REDIS_DB[config('cms.env')]);
        }

        if (!$this->configCMS) {
            $this->configCMS = config('cms.' . config('cms.env'));
        }
    }

    public function getConfigFromCache($configKey)
    {
        $config = $this->configCMS[$configKey];
        $key    = 'cms_' . $config[0] . '_' . $config[1];

        $data = $this->redisCMS->hget(CMS_CONFIG_KEY, $key);
        if (!$data) {
            @CmsCommon::query(CMS_SERVER[config('cms.env')] . $config[0] . '/' . $config[1], []);
            $data = $this->redisCMS->hget(CMS_CONFIG_KEY, $key);
        }

        return json_decode($data, true);
    }
}