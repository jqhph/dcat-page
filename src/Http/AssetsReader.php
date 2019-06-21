<?php

namespace Dcat\Page\Http;

use Illuminate\Http\Response;

/**
 * http请求静态文件处理类
 */
class AssetsReader
{
    /**
     * 文件类型返回配置
     *
     * @var array
     */
    protected static $mimeTypeMap = [
        'css' => 'text/css',
        'js' => 'text/javascript',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'html' => 'text/html',
        'htm' => 'text/html',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
    ];

    public static function addMimeType(string $extension, string $type)
    {
        if (!$extension || !$type) {
            return;
        }
        static::$mimeTypeMap[$extension] = $type;
    }

    /**
     * 读取请求文件
     *
     * @param $path
     * @return Response
     */
    public static function send($path)
    {
        if ($fullPath = static::findFile($path)) {
            return static::sendFile($fullPath);
        }

        abort(404);
    }

    /**
     * 发送文件
     *
     * @param string $path 文件完整路径
     * @return Response
     */
    public static function sendFile(string $path)
    {
        $modifiedAt = static::isNotModified($path);
//        if ($modifiedAt === true) {
//            return response(null, 304);
//        }

        $fileInfo = pathinfo($path);
        $extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
        $filename = isset($fileInfo['filename']) ? $fileInfo['filename'] : '';

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => '',
            'Connection' => 'keep-alive',
        ];

        if (!empty(static::$mimeTypeMap[$extension])) {
            $headers['Content-Type'] = static::$mimeTypeMap[$extension];
        } else {
            $headers['Content-Disposition'] = "attachment; filename=\"$filename\"";
        }

        if ($modifiedAt) {
            $headers['Last-Modified'] = $modifiedAt;
        }

        return response(file_get_contents($path), 200, $headers);
    }

    /**
     * 304判断
     *
     * @param string $fullPath
     * @return bool|string
     */
    protected static function isNotModified(string $path)
    {
        $request = request();
        $info = stat($path);
        $modifiedAt = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        $modifiedSince = $request->header('if-modified-since');
        if (!empty($modifiedSince) && $info) {
            if (strtolower($modifiedAt) === strtolower($modifiedSince)) {
                return true;
            }
        }

        return $modifiedAt;
    }

    /**
     * 根据请求地址查找文件路径
     *
     * @param string $path
     * @return null|string
     */
    public static function findFile(string $path)
    {
        if (is_file($path)) {
            return $path;
        }

        return null;
    }
}
