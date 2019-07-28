<?php
namespace addons\woofinder; // 注意命名空间规范

use woo\addons\Addons;

class WooFinder extends Addons // 需继承think\addons\Addons类
{
    /**
    * 插件安装方法
    * @return bool
    */
    public function install()
    {
        return true;
    }
    
    /**
    * 插件卸载方法
    * @return bool
    */
    public function uninstall()
    {
        return true;
    }
}
