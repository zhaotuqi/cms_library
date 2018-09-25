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
        'host'     => '10.9.103.15',
        'password' => 'FhPVixF4giXz0ECxUHiYF4UEAJCC0HNZ',
        'port'     => 6379,
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

    public function getConfigFromCache($configKey, $isFilter = true)
    {
        $config = $this->configCMS[$configKey];
        $key    = 'cms_' . $config[0] . '_' . $config[1];

        // 先从apcu中获取
        $data = self::getFromApcu($key);
        if (false === $data) {
            // 没有或错误的话从redis中获取
            $data = $this->redisCMS->hget(CMS_CONFIG_KEY, $key);
            if (!json_decode($data)) {
                return '配置不存在';
            }
            // 设置到apcu中
            self::setToApcu($key, $data);
        }

        if ($isFilter) {
            return json_decode(json_decode($data, true)['data'], true);
        }

        return json_decode($data, true);
    }

    static private function getFromApcu($key) {
        if (strlen($key) <= 0 || false == function_exists("apcu_exists")) {
            return false;
        }

        if (false == apcu_exists($key)) {
            return false;
        }

        $value = apcu_fetch($key, $success);
        if (false == $success) {
            return false;
        }
        return $value;
    }

    static private function setToApcu($key, $value) {
        if (strlen($key) <= 0) {
            return false;
        }
        $TTL = 600; // 10分钟
        return apcu_add($key, $value, $TTL);
    }

}