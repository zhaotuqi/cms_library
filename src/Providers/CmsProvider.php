<?php
/**
 * Created by PhpStorm.
 * User: liuhuajian
 * Date: 17/5/27
 * Time: 上午11:42
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\Cms;

class CmsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../Config/cms.php' => config_path('cms.php')]);

        $envContent = file_get_contents(base_path('.env'));
        if (false == strrpos($envContent, 'REDIS_CMS_ENV')) {
            if (App()->environment() == 'production' || App()->environment() == 'pro') {
                file_put_contents(base_path('.env'), PHP_EOL . 'REDIS_CMS_ENV=on' . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents(base_path('.env'), PHP_EOL . 'REDIS_CMS_ENV=qa' . PHP_EOL, FILE_APPEND);
            }
        }

        $databaseConfig = file_get_contents(config_path('database.php'));
        $dbConfigJson   = json_decode($databaseConfig, true);

        $dbConfigJson['redis']['cms_qa'] = [
            'host'     => '10.2.1.28',
            'password' => null,
            'port'     => 6380,
            'database' => 0
        ];
        $dbConfigJson['redis']['cms_on'] = [
            'host'     => '10.10.34.151',
            'password' => null,
            'port'     => 6379,
            'database' => 0
        ];

        file_put_contents(config_path('database.php'), json_encode($dbConfigJson));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Cms', function () {
            return new Cms();
        });
    }
}