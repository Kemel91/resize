<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/', 'App\Controller\IndexController@index');
Router::addRoute(['GET', 'POST'], '/resize', 'App\Controller\IndexController@resize');
Router::addRoute(['GET'], '/resize2', 'App\Controller\IndexController@resize2');

Router::get('/favicon.ico', function () {
    return '';
});
