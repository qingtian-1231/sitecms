<?php
namespace app\home\controller;

use app\common\controller\Home;

class SaleService extends Home
{
    /**
    * 初始化
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
}
