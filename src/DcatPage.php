<?php

namespace Dcat\Page;

use DcatPage as Fun;
use Illuminate\Support\Facades\Event;

class DcatPage
{
    const VERSION = '1.0.0';
    const NAME = 'dcat-page';

    /**
     * @var string
     */
    protected static $appName;

    /**
     * @var bool
     */
    protected static $isCompiling = false;

    /**
     * @var array
     */
    protected static $allAppNames = [];

    /**
     * 初始化
     *
     * @param string $app 应用名称
     * @param bool $isCompiling
     */
    public static function init($app, bool $isCompiling = false)
    {
        static::$appName = $app;
        $isCompiling && (static::$isCompiling = true);

        $config = [];
        if (is_file($path = Fun\path('config.php'))) {
            $config = (array)include $path;
        }

        config([static::NAME.'.'.$app => $config]);

    }

    /**
     * 判断项目是否在编译中
     *
     * @return bool
     */
    public static function isCompiling()
    {
        return static::$isCompiling;
    }

    /**
     * 监听编译事件
     *
     * @param $listener
     */
    public static function compiling($listener)
    {
        Event::listen('dcat-page:compiling', $listener);
    }

    /**
     * 触发编译中事件
     *
     * @param Console\CompileCommand $comman
     */
    public static function callCompiling(Console\CompileCommand $command)
    {
        Event::dispatch('dcat-page:compiling', $command);
    }

    /**
     * 获取当前应用名称
     *
     * @return string
     */
    public static function getCurrentAppName()
    {
        return static::$appName;
    }

    /**
     * 获取所有应用名称
     *
     * @return array
     */
    public static function getAllAppNames()
    {
        return static::$allAppNames ?: (static::$allAppNames = array_map(
            'basename',
            app('files')->directories(Fun\path())
        ));
    }

}
