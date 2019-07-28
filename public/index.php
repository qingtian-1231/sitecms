<?php
namespace think;

// 定义应用目录
define('DS', DIRECTORY_SEPARATOR);

// 项目根目录
define('ROOT_PATH', dirname(dirname(__FILE__)) . DS);

// 应用所在目录
define('APP_PATH', ROOT_PATH .'app' . DS);

// 应用可访问根目录 -- 就是入口index.php所在文件夹，默认public，可以修改
define('WWW_ROOT', dirname(__FILE__) . DS);

// woo系统核心目录
define('WOO_PATH', ROOT_PATH .'woo' . DS);

// 插件目录
define('ADDONS_PATH', ROOT_PATH . 'addons' . DS);

// 加载基础文件
require ROOT_PATH . 'thinkphp' . DS . 'base.php';
// 支持事先使用静态方法设置Request对象和Config对象

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// 执行应用并响应
Container::get('app', [APP_PATH])->run()->send();
