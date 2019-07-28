<?php
namespace app\home\controller;

use app\common\controller\Home;

class Error extends Home
{
    public function _empty($name)
    {
        return $this->message('error', '页面不存在');// 如果需要自行写400页面，就去掉这句代码，然后在线在error页面写400页面
        $this->fetch = '/error';
    }
}
