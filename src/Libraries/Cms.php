<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:39
 */

namespace App\Libraries;

use Predis\Client;

class Cms
{
    protected $redisCMS;
    protected $configCMS;

    public function __construct()
    {
        $redisHost = env('REDIS_HOST');
        $redisPassword = env('REDIS_PASSWORD');
        $redisPort = env('REDIS_PORT');
        $redisDB = env('REDIS_DB');

        $checkConfigMsg = "";
        $checkConfigMsg .= empty($redisHost) ? ".env文件 CMS选项： REDIS_HOST 未配置" . PHP_EOL : "";
        $checkConfigMsg .= (!isset($redisPassword)) ? ".env文件 CMS选项： REDIS_PASSWORD 未配置" . PHP_EOL : "";
        $checkConfigMsg .= empty($redisPort) ? ".env文件 CMS选项： REDIS_PORT 未配置" . PHP_EOL : "";
        $checkConfigMsg .= !isset($redisDB) ? ".env文件 CMS选项： REDIS_DB 未配置" . PHP_EOL : "";
        if (!empty($checkConfigMsg)) {
            throw new \Exception('CMS 配置选项检测结果：' . PHP_EOL . $checkConfigMsg);
        }

        $this->redisCMS = new Client([
            'host'     => $redisHost,
            'password' => $redisPassword,
            'port'     => $redisPort,
            'database' => $redisDB
        ]);
        $this->configCMS = config('cms.' . config('cms.env'));
    }

    public function getConfigFromCache($configKey, $isFilter = true)
    {
        if (null == $this->configCMS || !isset($this->configCMS[$configKey])) {
            return false;
        }
        $config = $this->configCMS[$configKey];
        if (count($config) != 2) {
            return false;
        }
        $key    = 'cms_' . $config[0] . '_' . $config[1];

        // 先从apcu中获取
        $data = self::getFromApcu($key);
        if (false === $data) {
            // 没有或错误的话从redis中获取
            $data = $this->redisCMS->hget("cms_keys", $key);
            if (!json_decode($data)) {
                return false;
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
        if (strlen($key) <= 0 || false == function_exists("apcu_exists")) {
            return false;
        }
        $TTL = 600; // 10分钟
        return apcu_add($key, $value, $TTL);
    }

}