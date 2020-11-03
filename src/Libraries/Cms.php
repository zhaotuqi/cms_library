<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:39
 */

namespace App\Libraries;

defined('CMS_REDIS_DB') or define('CMS_REDIS_DB', [
    'qa' => [
        'host' => 'fudao-predb-qa02-redis-rw.wenba100.com',
        'password' => 'FhPVixF4giXz0ECxUHiYF4UEAJCC0HNZ',
        'port' => 6379,
        'database' => 0
    ],
    'pre' => [
        'host' => 'fudao-predb-cms-redis-rw.wenba100.com',
        'password' => 'L8JgMGrQBt87qPuYMV4d',
        'port' => 6379,
        'database' => 0
    ],
    'pro' => [
        'host' => 'fudao-cms-redis-rw.wenba100.com',
        'password' => null,
        'port' => 6379,
        'database' => 0
    ]
]);

use  \Predis\Client;
use Log;
use Predis\Connection\ConnectionException;

class Cms
{
    protected $redisCMS;
    protected $configCMS;

    public function __construct()
    {
        $redisConfig = [];
        if (env('REDIS_CMS_IS_OPEN')) {
            //这里是为了移除上面写死的固定redis 地址， 请在项目的 .env 中添加 如下配置
            /*
                REDIS_CMS_IS_OPEN=1
                REDIS_CMS_HOST=
                REDIS_CMS_PASSWORD=NULL
                REDIS_CMS_PORT=6379
                REDIS_CMS_DB_NUMBER=0

             */
            $redisHost = env('REDIS_CMS_HOST');
            $redisPassword = env('REDIS_CMS_PASSWORD');
            $redisPort = env('REDIS_CMS_PORT');
            $redisDBNumber = env('REDIS_CMS_DB_NUMBER', 0);

            $checkConfigMsg = "";
            $checkConfigMsg .= empty($redisHost) ? ".env文件 CMS选项： REDIS_HOST 未配置" . PHP_EOL : "";
            $checkConfigMsg .= empty($redisPort) ? ".env文件 CMS选项： REDIS_PORT 未配置" . PHP_EOL : "";
            if (!empty($checkConfigMsg)) {
                throw new \Exception('CMS redis 配置项缺失告警：' . PHP_EOL . $checkConfigMsg);
            }
            $redisConfig = [
                'host' => $redisHost,
                'password' => $redisPassword,
                'port' => $redisPort,
                'database' => $redisDBNumber
            ];
        } else {
            $redisConfig = CMS_REDIS_DB[config('cms.env')];
        }
        $this->redisCMS = new Client($redisConfig);
        $cmsKey = 'cms.' . config('cms.env');
        $this->configCMS = config('cms.' . config('cms.env'));
        if (null == $this->configCMS) {
            // 如果对应的 env 在 cms.php 中不存在，报错
            Log::error('cms.php中不存在key:' . $cmsKey . '，提示信息所在文件' . __FILE__ . ':' . __LINE__);
            $this->configCMS = [];
        }
    }

    public function getConfigFromCache($configKey, $isFilter = true)
    {

        if (empty($this->configCMS[$configKey]) || (count($this->configCMS[$configKey]) !== 2)) {
            return [];
        }
        $config = $this->configCMS[$configKey];
        $key = 'cms_' . $config[0] . '_' . $config[1];

        // 先从apcu中获取
        $data = self::getFromApcu($key);
        if (false === $data) {
            //缓存里没有或错误的话从redis中获取
            try {
                $i = 0;
                A:
                $data = $this->redisCMS->hget("cms_keys", $key);
            } catch (\Exception $e) {
                if ($e instanceof ConnectionException) {
                    if (preg_match('/Error while reading line from the server/', $e->getMessage())) {
                        //元字符+限定符+修饰符
                       // echo '重试' . ($i + 1) . '次';
                        if ($i < 3) {
                            $i++;
                            goto A;
                        }
                    } else { //接着抛错误
                        throw new \Exception($e->getMessage(), $e->getCode());
                    }
                } else {//接着抛错误
                    throw new \Exception($e->getMessage(), $e->getCode());
                }
            }
            if (!json_decode($data, true)) {
                return [];
            }
            // 设置到apcu中
            self::setToApcu($key, $data);
        }

        if ($isFilter) {
            return json_decode(json_decode($data, true)['data'], true);
        }
        return json_decode($data, true);
    }

    static private function getFromApcu($key)
    {
        if (strlen($key) <= 0 || false == function_exists("apcu_exists")) {
            return [];
        }

        if (false == apcu_exists($key)) {
            return [];
        }

        $value = apcu_fetch($key, $success);
        if (false == $success) {
            return [];
        }
        return $value;
    }

    static private function setToApcu($key, $value)
    {
        if (strlen($key) <= 0 || false == function_exists("apcu_exists")) {
            return false;
        }
        $TTL = 600; // 10分钟
        return apcu_add($key, $value, $TTL);
    }

}
