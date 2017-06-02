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
        $content = file_get_contents(base_path('.env'));
        if (false == strrpos($content, 'REDIS_CMS_ENV')) {
            if (App()->environment() == 'production' || App()->environment() == 'pro') {
                file_put_contents(base_path('.env'), PHP_EOL . 'REDIS_CMS_ENV=pro' . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents(base_path('.env'), PHP_EOL . 'REDIS_CMS_ENV=qa' . PHP_EOL, FILE_APPEND);
            }
        }
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