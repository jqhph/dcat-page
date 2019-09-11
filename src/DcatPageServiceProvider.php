<?php

namespace Dcat\Page;

use Dcat\Admin\Admin;
use Dcat\Page\Admin\DcatPageExtension;
use Dcat\Page\Http\Middleware\Initialization;
use Illuminate\Support\ServiceProvider;
use Dcat\Page\Http\Controllers;
use Illuminate\Support\Facades\Route;

class DcatPageServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Console\CreateCommand::class,
        Console\CompileCommand::class,
        Console\IndexCommand::class,
    ];

    /**
     * @var array
     */
    protected $middlewares = [
        Initialization::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // 如果安装了 dcat admin，则注册dcat admin扩展
        if (class_exists(Admin::class)) {
            if (DcatPageExtension::disabled()) {
                return;
            }

            DcatPageExtension::make()->boot();
        }

        $this->registerRoutes();

        $this->loadViewsFrom(resource_path(DcatPage::NAME), DcatPage::NAME);

    }

    /**
     * Register routes.
     */
    public function registerRoutes()
    {
        \Route::group([
            'prefix'     => DcatPage::NAME,
            'middleware' => $this->middlewares,
        ], function () {
            Route::get('{app}/resource/{path}', Controllers\PageController::class.'@resource')->where('path', '.*');
            Route::get('{app}/docs/{version?}/{doc?}', Controllers\PageController::class.'@doc');
            Route::get('{app}/{view?}', Controllers\PageController::class.'@page')->where('view', '.*');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);

        if (!defined('DCAT_PAGE_VERSION')) {
            include __DIR__.'/helpers.php';
        }
    }

}
