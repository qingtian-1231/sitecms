<?php
namespace addons\example; // 注意命名空间规范

use woo\addons\Addons;

/**
* 插件测试
* @author byron sampson
*/
class Example extends Addons // 需继承think\addons\Addons类
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
    
    /**
    * 实现的test钩子方法
    */
    public function test()
    {
        return $this->fetch('test');
    }

}
