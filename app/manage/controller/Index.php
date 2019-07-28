<?php
namespace app\manage\controller;

use app\common\controller\Manage;

class Index extends Manage
{
    //初始化 需要调父级方法
    public function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    public function index()
    {
        $this->fetch = true;
    }
}
