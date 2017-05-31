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