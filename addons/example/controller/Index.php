<?php
namespace addons\example\controller; // 注意命名空间规范

use woo\addons\controller\Controller;

/**
* 插件测试
* @author byron sampson
*/
class Index extends Controller 
{
    
    function index()
    {
        return $this->fetch = true;
    }

}
